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
                v-if="! allVariantsActive"
                class="miniset-add"
                @click.prevent="addGroup">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 block opacity-25 hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>

        <div class="miniset-groups">

            <div class="miniset-compact">
                <miniset-classes-group
                    v-for="(group, index) in value"
                    v-if="index === selected"
                    :key="`group-${group._id}`"
                    :index="index"
                    :fields="fields"
                    :values="group"
                    :meta="meta.existing[group._id]"
                    :name="name"
                    :error-key-prefix="errorKeyPrefix"
                    @updated="updated"
                    @meta-updated="updateGroupMeta"
                    @removed="removed"
                    @focus="$emit('focus')"
                    @blur="$emit('blur')" />
            </div>
            
            <div
                class="miniset-create"
                v-if="addingGroup"
                >
                <div class="miniset-create-variants">
                    <button
                        class="btn"
                        v-for="(label, variant) in variants"
                        @click.prevent="commitGroup(variant)">
                        <span class="flex items-center" v-html="label || variant"></span>
                    </button>
                </div>
            </div>

        </div>        

    </div>

</template>

<script>
import uniqid from 'uniqid';
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

    provide() {
        return {
            miniset: {
                config: this.config,
                isReadOnly: this.isReadOnly,
                handle: this.handle,
                errorKeyPrefix: this.errorKeyPrefix,
            },
        };
    },

    computed: {

        allVariantsActive() {
            return Object.keys(this.variants).length === this.value.length - 1;
        },


        fields() {
            return this.config.fields;
        },

        showTabs() {
            return Object.keys(this.variants).length > 0
        },

        variantDefaultLabel() {
            return this.config.variant_default_label;
        },

        variants() {
            return this.config.variants;
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
            if(Object.keys(this.variants).length === 1){
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

            const group = _.chain(this.fields)
                .indexBy('handle')
                .mapObject(field => this.meta.defaults[field.handle])
                .value();

            group._id = id;
            group.variant = variant;

            this.updateGroupMeta(id, this.meta.new);
            this.update(this.sortGroups([...this.value, group]));

            this.$nextTick(() => {
                this.addingGroup = false;
                this.selected = _.findIndex(this.value, {'variant' : group.variant});
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

        updated(index, group) {
            this.update([
                ...this.value.slice(0, index),
                group,
                ...this.value.slice(index + 1)
            ]);
        },

        removed(index) {
            if (! confirm(__('Are you sure?'))) return;
                
            this.update([
                ...this.value.slice(0, index),
                ...this.value.slice(index + 1)
            ]);
        },

        blurred() {
            setTimeout(() => {
                if (!this.$el.contains(document.activeElement)) {
                    this.focused = false;
                }
            }, 1);
        },

        groupLabel(group) {
            return group.variant
                ? (this.variants[group.variant] || group.variant)
                : this.variantDefaultLabel ?? __('Default');
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
