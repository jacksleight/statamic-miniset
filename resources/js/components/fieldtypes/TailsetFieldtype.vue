<template>

    <div class="tailset-fieldtype-wrapper">

        <div class="tailset-fieldtype-tabs" v-if="showTabs">
            <div
                class="tailset-fieldtype-tab"
                :class="{   
                    'tailset-fieldtype-tab-active': selected === index
                }"
                v-for="(group, index) in value">
                <button
                    class="tailset-fieldtype-select"
                    :class="{   
                        'tailset-fieldtype-select-removeable': index !== 0 && index === selected
                    }"
                    v-text="groupLabel(group)"
                    @click.prevent="selectGroup(index)">
                </button>
                <button
                    class="tailset-fieldtype-remove"
                    v-if="index !== 0 && index === selected"
                    @click="removeGroup(index)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 block opacity-25 hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <button
                class="tailset-fieldtype-add"
                @click.prevent="addGroup">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 block opacity-25 hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>

        <div class="tailset-fieldtype-groups">

            <div class="tailset-compact">
                <tailset-group
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
                class="tailset-fieldtype-create"
                v-if="addingGroup"
                >
                <div class="tailset-fieldtype-create-variants">
                    <button
                        class="btn"
                        v-for="(label, variant) in variants"
                        @click.prevent="commitGroup(variant)">
                        <span v-text="label"></span>
                    </button>
                </div>
            </div>

        </div>        

    </div>

</template>

<script>
import uniqid from 'uniqid';
import TailsetGroup from './TailsetGroup.vue';

export default {

    mixins: [
        Fieldtype,
        TailsetGroup,
    ],

    data() {
        return {
            selected: 0,
            addingGroup: false,
            focused: false,
        }
    },

    computed: {

        fields() {
            return this.config.fields;
        },

        variants() {
            return this.config.variants;
        },

        showTabs() {
            return Object.keys(this.variants).length > 0
        },

    },

    reactiveProvide: {
        name: 'tailset',
        include: ['config', 'isReadOnly', 'handle', 'errorKeyPrefix']
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
            this.addingGroup = true;
        },

        commitGroup(variant) {
            
            const variants = [variant];
            
            const existing = this.value.findIndex(group => _.isEqual(group.variants, variants));
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
            group.variants = [variant];

            this.updateGroupMeta(id, this.meta.new);
            this.update([...this.value, group]);

            this.$nextTick(() => {
                this.addingGroup = false;
                this.selected = this.value.length - 1;
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
            return group.variants
                ? group.variants.map(v => this.variants[v] || v).join(', ')
                : 'Default';
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
