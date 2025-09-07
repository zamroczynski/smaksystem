<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import type { ProductCreateProps, ProductFormType } from '@/types';
import type { InertiaForm } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';

const { form, productParams } = defineProps<{
    form: InertiaForm<ProductFormType>; 
    productParams: Omit<ProductCreateProps, 'flash' | 'breadcrumbs'>;
}>();

const emit = defineEmits(['submit']);
</script>

<template>
    <form @submit.prevent="emit('submit')" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <Label for="name">Nazwa <span class="text-red-500">*</span></Label>
                <Input id="name" type="text" v-model="form.name" required />
                <p v-if="form.errors.name" class="text-sm text-red-500 mt-1">{{ form.errors.name }}</p>
            </div>
            <div>
                <Label for="sku">SKU (Kod produktu)</Label>
                <Input id="sku" type="text" v-model="form.sku" />
                <p v-if="form.errors.sku" class="text-sm text-red-500 mt-1">{{ form.errors.sku }}</p>
            </div>
        </div>

        <div>
            <Label for="description">Opis</Label>
            <Textarea id="description" v-model="form.description" />
            <p v-if="form.errors.description" class="text-sm text-red-500 mt-1">{{ form.errors.description }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <Label for="product_type_id">Typ produktu <span class="text-red-500">*</span></Label>
                <Select v-model="form.product_type_id">
                    <SelectTrigger><SelectValue placeholder="Wybierz typ..." /></SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="pt in productParams.productTypes" :key="pt.id" :value="pt.id">{{ pt.name }}</SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.product_type_id" class="text-sm text-red-500 mt-1">{{ form.errors.product_type_id }}</p>
            </div>
            <div>
                <Label for="category_id">Kategoria <span class="text-red-500">*</span></Label>
                <Select v-model="form.category_id">
                    <SelectTrigger><SelectValue placeholder="Wybierz kategorię..." /></SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="cat in productParams.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</SelectItem>
                    </SelectContent>
                </Select>
                 <p v-if="form.errors.category_id" class="text-sm text-red-500 mt-1">{{ form.errors.category_id }}</p>
            </div>
            <div>
                <Label for="unit_of_measure_id">Jednostka miary <span class="text-red-500">*</span></Label>
                <Select v-model="form.unit_of_measure_id">
                    <SelectTrigger><SelectValue placeholder="Wybierz jednostkę..." /></SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="uom in productParams.unitsOfMeasure" :key="uom.id" :value="uom.id">{{ uom.name }} ({{ uom.symbol }})</SelectItem>
                    </SelectContent>
                </Select>
                 <p v-if="form.errors.unit_of_measure_id" class="text-sm text-red-500 mt-1">{{ form.errors.unit_of_measure_id }}</p>
            </div>
            <div>
                <Label for="vat_rate_id">Stawka VAT <span class="text-red-500">*</span></Label>
                <Select v-model="form.vat_rate_id">
                    <SelectTrigger><SelectValue placeholder="Wybierz stawkę..." /></SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="vat in productParams.vatRates" :key="vat.id" :value="vat.id">{{ vat.name }} ({{ vat.rate }}%)</SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.vat_rate_id" class="text-sm text-red-500 mt-1">{{ form.errors.vat_rate_id }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4">
             <div class="space-y-2">
                <Label for="is_sellable">Produkt przeznaczony do sprzedaży?</Label>
                <div class="flex items-center space-x-2">
                    <Switch id="is_sellable" v-model="form.is_sellable" />
                    <span>{{ form.is_sellable ? 'Tak' : 'Nie' }}</span>
                </div>
                <p v-if="form.errors.is_sellable" class="text-sm text-red-500 mt-1">{{ form.errors.is_sellable }}</p>
             </div>
             <div class="space-y-2">
                <Label for="is_inventoried">Czy śledzić stan magazynowy?</Label>
                 <div class="flex items-center space-x-2">
                    <Switch id="is_inventoried" v-model="form.is_inventoried" />
                    <span>{{ form.is_inventoried ? 'Tak' : 'Nie' }}</span>
                </div>
                <p v-if="form.errors.is_inventoried" class="text-sm text-red-500 mt-1">{{ form.errors.is_inventoried }}</p>
             </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
             <div v-if="form.is_sellable">
                <Label for="selling_price">Cena sprzedaży (brutto)</Label>
                <Input id="selling_price" type="number" step="0.01" v-model="form.selling_price" placeholder="0.00" />
                <p v-if="form.errors.selling_price" class="text-sm text-red-500 mt-1">{{ form.errors.selling_price }}</p>
            </div>
            <div>
                <Label for="default_purchase_price">Domyślna cena zakupu (netto)</Label>
                <Input id="default_purchase_price" type="number" step="0.01" v-model="form.default_purchase_price" placeholder="0.00" />
                <p v-if="form.errors.default_purchase_price" class="text-sm text-red-500 mt-1">{{ form.errors.default_purchase_price }}</p>
            </div>
        </div>
    </form>
</template>