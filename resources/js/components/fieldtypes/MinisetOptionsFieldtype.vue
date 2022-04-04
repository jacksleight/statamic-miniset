<template>

    <div class="miniset-options">

        <div class="miniset-groups">

            <div class="miniset-compact">
                <publish-fields-container>
                    <set-field
                        v-for="field in fields"
                        v-show="showField(field)"
                        :key="field.handle"
                        :field="field"
                        :meta="meta[field.handle]"
                        :value="value[field.handle]"
                        :parent-name="name"
                        :set-index="index"
                        :errors="errors(field.handle)"
                        :error-key-prefix="field.handle"
                        :read-only="isReadOnly"
                        @updated="updated(field.handle, $event)"
                        @meta-updated="metaUpdated(field.handle, $event)"
                        @focus="$emit('focus')"
                        @blur="$emit('blur')"
                    />
                </publish-fields-container>
            </div>

        </div>
        
    </div>

</template>

<script>
import SetField from '../../../../vendor/statamic/cms/resources/js/components/fieldtypes/replicator/Field.vue';
import { ValidatesFieldConditions } from '../../../../vendor/statamic/cms/resources/js/components/field-conditions/FieldConditions.js';

export default {

    mixins: [
        Fieldtype,
        ValidatesFieldConditions,
    ],

    components: { SetField },

    data() {
        return {
            focused: false,
        }
    },

    computed: {

        fields() {
            return this.config.fields;
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

        updated(handle, value) {
            let group = JSON.parse(JSON.stringify(this.value));
            group[handle] = value;
            this.update(group);
        },

        errors(handle) {
            const state = this.$store.state.publish[this.storeName];
            if (! state) return [];
            return state.errors[handle] || [];
        },

        blurred() {
            setTimeout(() => {
                if (!this.$el.contains(document.activeElement)) {
                    this.focused = false;
                }
            }, 1);
        },

    }

}
</script>
