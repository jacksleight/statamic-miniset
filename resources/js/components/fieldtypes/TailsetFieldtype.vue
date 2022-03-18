<template>

    <div class="tailset-fieldtype-wrapper">

        <div class="tailset-fieldtype-tabs">
            <button
                class="tailset-fieldtype-tab"
                :class="{   
                    'tailset-fieldtype-tab-active': selected === index
                }"
                v-text="groupLabel(group)"
                v-for="(group, index) in value"
                @click.prevent="selectGroup(index)" />
            <button
                class="tailset-fieldtype-add"
                @click.prevent="addGroup">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-25" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>

        <div class="tailset-fieldtype-inner">
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
                :can-delete="canDelete"
                @updated="updated"
                @meta-updated="updateGroupMeta"
                @removed="removed"
                @focus="$emit('focus')"
                @blur="$emit('blur')" />
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

        canDelete() {
            return ! this.isReadOnly && this.value.length > 1;
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
            const id = uniqid();

            const group = _.chain(this.fields)
                .indexBy('handle')
                .mapObject(field => this.meta.defaults[field.handle])
                .value();

            group._id = id;

            this.updateGroupMeta(id, this.meta.new);
            this.update([...this.value, group]);
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

<style>
.tailset-fieldtype-tabs {
    font-size: 12px !important;
    display: flex;
    margin-bottom: -1px;
}
.tailset-fieldtype-tab {
    font-size: 12px !important;
    border-width: 1px !important;
    border-color: #dde3e9;
    border-radius: 3px 3px 0 0 !important;
    background-color: white;
    padding: 4px 8px !important;
}
.tailset-fieldtype-tab:not(:first-child) {
    border-left: 0;
}
.tailset-fieldtype-tab-active {
    border-bottom-color: transparent;
    background-color: #fafcff;
}

.tailset-fieldtype-add {
    font-size: 12px !important;
    border-width: 1px 1px 0 1px !important;
    border-color: transparent;
    padding: 0 0.25rem;
}

.tailset-fieldtype-inner {
    font-size: 12px !important;
    border-width: 1px !important;
    border-color: #dde3e9;
    border-radius: 0 3px 3px 3px !important;
    background-color: #fafcff;
}
.tailset-fieldtype-inner * {
    font-size: inherit !important;
}

.tailset-fieldtype-inner .publish-fields {
    padding: 0.6rem !important;
}
.tailset-fieldtype-inner .publish-field {
    padding: 0.5rem !important;
}

.tailset-fieldtype-inner .publish-fields .form-group > label {
    line-height: 0.8;
    margin-bottom: 7px;
}

.tailset-fieldtype-inner .select-fieldtype .vs__dropdown-toggle {
    height: 1.625rem !important;
}
.tailset-fieldtype-inner .select-fieldtype .vs__open-indicator {
    height: 1.625rem !important;
    padding-left: 4px;
    padding-right: 4px;
}
.tailset-fieldtype-inner .select-fieldtype .vs__selected-options {
    padding: 4px 6px !important;
    background-color: white;
}
.tailset-fieldtype-inner .select-fieldtype .vs__selected-options-outside .vs__selected {
    margin-top: 4px !important;
    margin-right: 4px !important;
    padding-top: 2px;
    padding-left: 4px;
    padding-right: 4px;
}
.tailset-fieldtype-inner .select-fieldtype .vs__selected-options-outside .vs__deselect {
    margin-top: -2px;
    padding-top: 1px;
}
.tailset-fieldtype-inner .select-fieldtype .vs__dropdown-menu {
    top: 32px;
}
.tailset-fieldtype-inner .select-fieldtype .vs__dropdown-option {
    padding: 4px 6px !important;
}

.tailset-fieldtype-inner .button_group-fieldtype .btn-group {
    height: 1.625rem !important;
}
.tailset-fieldtype-inner .button_group-fieldtype .btn {
    padding: 4px 6px !important;
    height: 1.625rem !important;
}

.tailset-fieldtype-inner .text-fieldtype .input-group {
    height: 1.625rem !important;
}
.tailset-fieldtype-inner .text-fieldtype .input-text {
    padding: 4px 6px !important;
    background-color: white;
    height: 1.625rem !important;
}
</style>