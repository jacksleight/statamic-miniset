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

    mixins: [
        Fieldtype
    ],

    components: {
        Fields,
        FieldsProvider,
    },

    computed: {

        values() {
            return this.value;
        },

        extraValues() {
            return {};
        },

        fields() {
            return this.config.fields.map(field => ({...field, size: 'xs'}));
        },

    },

    methods: {

        updated(handle, value) {
            this.update({
                ...this.value,
                [handle]: value,
            });
        },

        updateMeta(handle, value) {
            this.$emit('meta-updated', { ...this.meta, [handle]: value });
        },

    }

}
</script>
