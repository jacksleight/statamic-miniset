import MinisetClassesFieldtype from './components/Fieldtypes/MinisetClassesFieldtype.vue';

Statamic.booting(() => {

    Statamic.$components.register('miniset_classes-fieldtype', MinisetClassesFieldtype);

});
