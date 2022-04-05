import MinisetFieldtype from './components/Fieldtypes/MinisetFieldtype.vue';
import MinisetClassesFieldtype from './components/Fieldtypes/MinisetClassesFieldtype.vue';

Statamic.booting(() => {

    Statamic.$components.register('miniset-fieldtype', MinisetFieldtype);
    Statamic.$components.register('miniset_classes-fieldtype', MinisetClassesFieldtype);

});
