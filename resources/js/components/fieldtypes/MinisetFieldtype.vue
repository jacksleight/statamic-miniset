<template>

    <div class="miniset miniset-values">

        <div class="miniset-groups">

            <FieldsProvider
                :fields="fields"
                :field-path-prefix="fieldPathPrefix ? `${fieldPathPrefix}.${handle}` : handle"
                :meta-path-prefix="metaPathPrefix ? `${metaPathPrefix}.${handle}` : handle"
            >
                <Fields class="miniset-compact" />
            </FieldsProvider>

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
            return this.config.fields;
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
