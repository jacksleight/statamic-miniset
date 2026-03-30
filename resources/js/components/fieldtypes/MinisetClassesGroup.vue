<template>

    <FieldsProvider
        :fields="fields"
        :field-path-prefix="fieldPathPrefix"
        :meta-path-prefix="metaPathPrefix"
    >
        <Fields class="miniset-compact" />
    </FieldsProvider>

</template>

<script>
import { PublishFields as Fields, PublishFieldsProvider as FieldsProvider } from '@statamic/cms/ui';

export default {

    components: { Fields, FieldsProvider },

    props: {
        fields: {
            type: Array,
            required: true,
        },
        fieldPathPrefix: {
            type: String,
            required: true,
        },
        metaPathPrefix: {
            type: String,
            required: true,
        },
    },

}
</script>
