<template>

    <div class="miniset miniset-values">

        <div class="miniset-groups">

            <div class="miniset-compact">
                <FieldsProvider
                    :fields="fields"
                    :field-path-prefix="fieldPathPrefix ? `${fieldPathPrefix}.${handle}` : handle"
                    :meta-path-prefix="metaPathPrefix ? `${metaPathPrefix}.${handle}` : handle"
                >
                    <Fields class="p-4" />
                </FieldsProvider>
            </div>

        </div>
        
    </div>

</template>

<script>
import { FieldtypeMixin as Fieldtype } from '@statamic/cms';
import { PublishFields as Fields, PublishFieldsProvider as FieldsProvider } from '@statamic/cms/ui';

export default {

    mixins: [Fieldtype],

    components: { Fields, FieldsProvider },

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

        errorKey(handle) {
            return `${this.handle}.${handle}`;
        },

        errors(handle) {
            const state = this.$store.state.publish[this.storeName];
            if (! state) return [];
            return state.errors[this.errorKey(handle)] || [];
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
