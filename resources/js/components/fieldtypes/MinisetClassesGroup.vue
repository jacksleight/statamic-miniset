<template>

    <publish-fields-container>
        <set-field
          v-for="field in fields"
          v-show="showField(field)"
          :key="field.handle"
          :field="field"
          :meta="meta[field.handle]"
          :value="values[field.handle]"
          :parent-name="name"
          :set-index="index"
          :errors="errors(field.handle)"
          :error-key-prefix="errorKey(field.handle)"          
          :read-only="miniset.isReadOnly"
          @updated="updated(field.handle, $event)"
          @meta-updated="metaUpdated(field.handle, $event)"
          @focus="$emit('focus')"
          @blur="$emit('blur')"
        />
      </publish-fields-container>

</template>

<script>
import SetField from '../../../../vendor/statamic/cms/resources/js/components/fieldtypes/replicator/Field.vue';
import { ValidatesFieldConditions } from '../../../../vendor/statamic/cms/resources/js/components/field-conditions/FieldConditions.js';

export default {

    mixins: [
        ValidatesFieldConditions,
    ],

    components: { SetField },

    inject: ['miniset'],

    props: {
        index: {
            type: Number,
            required: true
        },
        fields: {
            type: Array,
            required: true
        },
        values: {
            type: Object,
            required: true
        },
        meta: {
            type: Object,
            required: true
        },
        name: {
            type: String,
            required: true
        },
        errorKeyPrefix: {
            type: String
        },
    },

    methods: {

        updated(handle, value) {
            let group = JSON.parse(JSON.stringify(this.values));
            group[handle] = value;
            this.$emit('updated', this.index, group);
        },

        metaUpdated(handle, value) {
            let meta = clone(this.meta);
            meta[handle] = value;
            this.$emit('meta-updated', meta);
        },

        errorKey(handle) {
            return `${this.errorKeyPrefix}.${this.index}.${handle}`;
        },

        errors(handle) {
            const state = this.$store.state.publish[this.storeName];
            if (! state) return [];
            return state.errors[this.errorKey(handle)] || [];
        }

    },

}
</script>
