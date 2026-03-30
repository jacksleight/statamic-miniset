<template>

    <div class="miniset miniset-classes">

        <div class="miniset-tabs" v-if="showTabs">
            <div
                class="miniset-tab"
                :class="{
                    'miniset-tab-active': selected === index
                }"
                v-for="(group, index) in value">
                <button
                    class="miniset-select flex items-center"
                    :class="{   
                        'miniset-select-removeable': index !== 0 && index === selected
                    }"
                    v-html="groupLabel(group)"
                    @click.prevent="selectGroup(index)">
                </button>
                <button
                    class="miniset-remove"
                    v-if="index !== 0 && index === selected"
                    @click="removeGroup(index)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 block opacity-25 hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <button
                v-if="! allVariantsAdded"
                class="miniset-add"
                @click.prevent="addGroup">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 block opacity-25 hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>

        <div class="miniset-groups">

            <template v-for="(group, index) in value" :key="`group-${group._id}`">
                <MinisetClassesGroup
                    v-if="index === selected && !addingGroup"
                    :fields="fields"
                    :field-path-prefix="`${fieldPathPrefix ? `${fieldPathPrefix}.${handle}` : handle}.${index}`"
                    :meta-path-prefix="`${metaPathPrefix ? `${metaPathPrefix}.${handle}` : handle}.existing.${group._id}`" />
            </template>

            <div
                class="miniset-create"
                v-if="addingGroup"
                >
                <div class="miniset-create-variants">
                    <ui-button
                        size="sm"
                        v-for="(label, variant) in variants"
                        @click.prevent="commitGroup(variant)"
                        v-html="label || variant" />
                </div>
            </div>

        </div>        

    </div>

</template>

<script>
import uniqid from 'uniqid';
import { FieldtypeMixin as Fieldtype } from '@statamic/cms';
import MinisetClassesGroup from './MinisetClassesGroup.vue';

export default {

    mixins: [
        Fieldtype,
    ],

    components: {
        MinisetClassesGroup,
    },

    data() {
        return {
            selected: 0,
            addingGroup: false,
            focused: false,
        }
    },

    computed: {

        allVariantsAdded() {
            return this.variantCount === this.value.length - 1;
        },

        fields() {
            return this.config.fields;
        },

        showTabs() {
            return this.variantCount > 0
        },

        defaultLabel() {
            return this.config.default_label || __('Default');
        },

        variants() {
            return this.config.variants;
        },

        variantCount() {
            return Object.keys(this.variants).length;
        },

    },

    watch: {

        focused(focused, oldFocused) {
            if (focused === oldFocused) return;

            if (focused) return this.$emit('focus');

            setTimeout(() => {
                if (!this.$el.contains(document.activeElement)) {
                    this.$emit('blur');
                }
            }, 1);
        }

    },

    methods: {

        selectGroup(index) {
            this.selected = index;
        },

        addGroup() {
            if (this.variantCount === 1) {
                this.commitGroup(Object.keys(this.variants)[0]);
            } else {
                this.addingGroup = true;
            }
        },

        commitGroup(variant) {
            const existing = this.value.findIndex(group => group.variant === variant);
            if (existing !== -1) {
                this.addingGroup = false;
                this.selected = existing;
                return;
            } 

            const id = uniqid();

            const group = Object.fromEntries(
                this.fields.map(field => [field.handle, this.meta.defaults[field.handle]])
            );

            group._id = id;
            group.variant = variant;

            this.updateGroupMeta(id, this.meta.new);
            this.update(this.sortGroups([...this.value, group]));

            this.$nextTick(() => {
                this.addingGroup = false;
                this.selected = this.value.findIndex(v => v.variant === group.variant);
            });
        },

        sortGroups(groups) {
            return groups.sort((a, b) => {
                if (!a.variant || !b.variant) {
                    return 0;
                }
                return this.meta.variant_indexes[a.variant] - this.meta.variant_indexes[b.variant];
            });
        },
    
        removeGroup(index) {
            if (! confirm(__('Are you sure?'))) return;
                
            this.update([
                ...this.value.slice(0, index),
                ...this.value.slice(index + 1)
            ]);

            this.$nextTick(() => {
                this.selected = Math.min(this.selected, this.value.length - 1);
                document.activeElement.blur();
            });
        },

        groupLabel(group) {
            return group.variant
                ? (this.variants[group.variant] || group.variant)
                : this.defaultLabel;
        },

        updateGroupMeta(group, value) {
            this.updateMeta({
                ...this.meta,
                existing: {
                    ...this.meta.existing,
                    [group]: value
                }
            });
        },

    }

}
</script>
