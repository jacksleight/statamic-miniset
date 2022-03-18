import TailsetFieldtype from './components/Fieldtypes/TailsetFieldtype.vue';
import TailsetGroup from './components/Fieldtypes/TailsetGroup.vue';

Statamic.booting(() => {

    Statamic.$components.register('tailset-fieldtype', TailsetFieldtype);
    Statamic.$components.register('tailset-group', TailsetGroup);

});
