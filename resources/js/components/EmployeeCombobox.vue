<!-- resources/js/Components/EmployeeCombobox.vue -->
<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import type { Ref } from 'vue'
import axios from 'axios'
import { Check, ChevronsUpDown } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import {
  Combobox,
  ComboboxAnchor,
  ComboboxEmpty,
  ComboboxGroup,
  ComboboxInput,
  ComboboxItem,
  ComboboxItemIndicator,
  ComboboxList,
  ComboboxTrigger,
} from '@/components/ui/combobox';
// import { type AcceptableValue } from 'reka-ui'

interface Employee {
  value: number
  label: string 
}

const props = defineProps<{
    modelValue: number | null
    initialEmployee?: Employee | null
    startOpen?: boolean
}>()

const emit = defineEmits(['update:modelValue'])

const searchTerm = ref('')
const isLoading = ref(false)
const open = ref(props.startOpen || false)

const selectedEmployee = ref<Employee | null>(props.initialEmployee || null)
const options: Ref<Employee[]> = ref(props.initialEmployee ? [props.initialEmployee] : [])

let debounceTimer: number;

function handleUpdate(newValue: unknown): void {
    if (typeof newValue === 'object' && newValue !== null && 'value' in newValue && 'label' in newValue) {
        const employee = newValue as Employee;
        selectedEmployee.value = employee;
        emit('update:modelValue', employee.value);
    } else if (newValue === null) {
        selectedEmployee.value = null;
        emit('update:modelValue', null);
    }
}

const displayLabel = computed(() => {
    return selectedEmployee.value?.label ?? 'Wybierz pracownika...'
})

const debounce = (func: () => void, delay: number) => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => func(), delay) as any;
}

const fetchEmployees = async () => {
    isLoading.value = true
    try {
        const response = await axios.get(route('users.search', { query: searchTerm.value }))
        const searchResults: Employee[] = response.data.data;

        if (selectedEmployee.value) {
            const isSelectedInResults = searchResults.some(
                (user) => user.value === selectedEmployee.value!.value
            );
            if (!isSelectedInResults) {
                searchResults.unshift(selectedEmployee.value);
            }
        }

        options.value = searchResults;

    } catch (error) {
        console.error('Błąd podczas wyszukiwania pracowników:', error)
        options.value = []
    } finally {
        isLoading.value = false
    }
}

watch(open, (isOpen) => {
    if (isOpen && options.value.length <= 1) {
        fetchEmployees();
    }
}, { immediate: true });

watch(searchTerm, () => {
  debounce(fetchEmployees, 300)
})
</script>

<template>
  <Combobox
    :model-value="selectedEmployee"
    @update:model-value="handleUpdate($event)"
    v-model:open="open"
    class="w-full"
  >
    <ComboboxAnchor>
      <ComboboxTrigger as-child>
        <Button variant="outline" class="w-full justify-between">
          {{ displayLabel }}
          <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
        </Button>
      </ComboboxTrigger>
    </ComboboxAnchor>

    <ComboboxList :class="cn('w-[--radix-combobox-trigger-width] p-0')">
      <div class="p-1">
          <ComboboxInput v-model="searchTerm" placeholder="Szukaj..." class="h-9 w-full" />
      </div>
      <ComboboxEmpty>
        {{ isLoading ? 'Szukanie...' : 'Nie znaleziono pracownika.' }}
      </ComboboxEmpty>
      <ComboboxGroup>
        <ComboboxItem
          v-for="option in options"
          :key="option.value"
          :value="option"
        >
          {{ option.label }}
          <ComboboxItemIndicator>
            <Check :class="cn('ml-auto h-4 w-4')" />
          </ComboboxItemIndicator>
        </ComboboxItem>
      </ComboboxGroup>
    </ComboboxList>
  </Combobox>
</template>