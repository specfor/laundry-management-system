<template>
    <!-- Add new Account Model -->
    <InputTemplateModel ref="addTaxModelRef" :initial-value="AddTaxModelInitialValue" :validator="addTaxValidator">
        <template #inputs="{ data, errors, setValue, mergeValue, value }">
            <TextInput label="Tax Name" :error="errors.taxName" :modelValue="value.taxName"
                @update:modelValue="setValue({ ...value, taxName: $event })"></TextInput>

            <TextInput label="Tax Rate" :error="errors.taxRate" :modelValue="value.taxRate"
                @update:modelValue="setValue({ ...value, taxRate: $event })"></TextInput>

            <div class="form-control w-full max-w-xs m-0">
                <label class="label">
                    <span class="label-text">Tax type Description</span>
                </label>
                <!-- @vue-ignore -->
                <textarea :value="value.description" @input="setValue({ ...value, description: $event.target.value })"
                    placeholder="Description" class="textarea textarea-bordered textarea-sm w-full max-w-xs"
                    :class="{ 'input-error': errors.description }"></textarea>
                <!-- <input type="text" placeholder="Account Description" class="input input-sm input-bordered w-full max-w-xs" 
                    :class="{ 'input-error': errors.description }" /> -->
                <label class="label">
                    <span class="label-text-alt"></span>
                    <span v-if="errors.description" class="label-text-alt">{{ errors.description }}</span>
                </label>
            </div>

            <!-- <TextInput label="Account Description" :error="errors.description" :modelValue="value.description"
                @update:modelValue="setValue({ ...value, description: $event })"></TextInput> -->
        </template>
    </InputTemplateModel>

    <!-- Edit Tax type model -->
    <ModelWithInput ref="modelWithInputRef">
        <template #input="{ data, value, setValue }">
            <el-select :model-value="value" @change="e => setValue(e)" class="h-full border-none" filterable
                placeholder="Tax Type">
                <el-option v-for="item in data" :key="item.tax_id" :label="item.name" :value="item.tax_id">
                    <span class="float-left mr-2">{{ item.name }}</span>
                    <span class="text-gray-400 float-right">
                        {{ toReadable(item.tax_rate) + "%" }}
                    </span>
                </el-option>
                <template #empty>
                    <span>No results ...</span>
                </template>
            </el-select>
        </template>
    </ModelWithInput>

    <!-- Action Confirmation model for Deleting accounts -->
    <ConfirmActionModel ref="confirmActionModelRef"></ConfirmActionModel>

    <Suspense>
        <RecordTable ref="recordTableRef" :command-handler="handleCommand" :get-records="loadTaxes"
            :column-slots="['name', 'rate', 'using']" :search-fields="['name', 'description']"
            :sorter="sorter" :extra-data-fetcher="fetchAccounts" :id-property="'tax_id'">

            <template #table-headers>
                <th>Name</th>
                <th>Rate</th>
                <th>Used by</th>
            </template>

            <template #row-actions="{ record }">
                <el-dropdown-item :icon="Delete" command="delete">Delete</el-dropdown-item>
                <el-dropdown-item :icon="Edit" command="edit">Edit</el-dropdown-item>
            </template>

            <template #header-actions="{ selectedRecords }">
                <TransitionGroup name="header-buttons">
                    <div class="tooltip" v-show="selectedRecords.length > 0" data-tip="Delete Selected Tax types"
                        key="del-but">
                        <button :disabled="selectedRecords.length == 0" @click="handleDeleteSelectedTaxTypes(selectedRecords)"
                            class="btn btn-square btn-error btn-sm">
                            <svg class="w-4 h-4 text-gray-800 dark:text-white stroke-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M1 5h16M7 8v8m4-8v8M7 1h4a1 1 0 0 1 1 1v3H6V2a1 1 0 0 1 1-1ZM3 5h12v13a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5Z" />
                            </svg>
                        </button>
                    </div>
                    <button class="btn btn-sm btn-neutral" key="add-acc-but" @click="handleAddTax">
                        <svg class="w-4 h-4 text-gray-800 dark:text-white stroke-white" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16" />
                        </svg>
                        New Tax Type
                    </button>
                </TransitionGroup>
            </template>

            <template #rate="{ record }">
                <span class="text-sm text-slate-700">
                    {{ toReadable(record.tax_rate) }}%
                </span>
            </template>

            <template #name="{ record }">
                <span class="text-base font-medium">
                    {{ record.name }} - {{ record.tax_id }}
                </span>
                <p class="text-ellipsis">{{ record.description.length > 100 ? record.description.slice(0, 100)
                    +
                    "..." : record.description }} </p>
            </template>

            <template #using="{ record, extra }">
                <span>In use by <span class="font-medium">{{accountsUsingTaxType(record.tax_id, extra ?? [])}}</span></span>
            </template>
        </RecordTable>
        <template #fallback>
            <div class="fixed top-1/2 left-1/2 flex flex-row justify-center">
                <span class="loading loading-spinner loading-lg"></span>
            </div>
        </template>
    </Suspense>
</template>

<script lang="ts" setup>
// @ts-check
import RecordTableGeneric, { type ArrangementData } from '../components/RecordTable.vue';
import ModelWithInputGeneric from '../components/models/ModelWithInput.vue';
import ConfirmActionModelGeneric from '../components/models/ConfirmActionModel.vue';
import InputTemplateModelGeneric from '../components/models/InputTemplateModel.vue';
import { useFinancialAccounts } from '../composibles/entity/financial-accounts';
import { ElDropdownItem, ElSelect, ElOption, ElPopover, ElTreeSelect } from "element-plus";
import { Delete, TakeawayBox, Edit, EditPen } from '@element-plus/icons-vue';
import { useTaxes } from '../composibles/entity/taxes';
import { isProperNumberString, toReadable } from '../util/decimal-util';
import { Decimal } from 'decimal.js'
import { computed, ref, watch } from 'vue';
import type { FinancialAccount, GenericComponentInstance, Tax } from '../types';
import { useBatchFetch } from '../composibles/batch-fetch';
import { accountTypesTree } from '../util/account-types';
import Fuse from 'fuse.js';
import TextInput from '../components/inputs/TextInput.vue';
import { useRouteQuery } from '@vueuse/router'

// Type Inside the record table with some extra data
type ModifiedTax = Tax & ArrangementData;
type AddNewTaxModelExtraData = { existingTaxNames: string[] };
interface AddTaxModelInitialValueType {
    taxName?: string,
    description?: string,
    taxRate?: string
}

// Defining generics of above imported generic elements
// https://play.vuejs.org/#eNp9U8Fu2zAM/RVCl3RAER96C5wAWxEM22ErstyiHDybTtTakiDKTQLD/z5KTmN72HKxRfGR4iMfW/HZ2vl7g2IhUsqdsh4IfWOhyvRhKYUnKVZSq9oa56EFOmZVZU4bLKGD0pkaZhw9k/qG+YoancqfTW17gBTzJFjhGSkiUnt0ZZYjPDfkTb3B3LgCWqkBVLEA8k7pQ7B0VuNgdyE4N5o8KI81LIeQmSpmj0PELPzCRQeUeUWlQpo8Ns1EnGpglo6Bu/3qYRcw+09DTO+KFJdjwpNIblua9D3lIxucxFaZR7YA0lGORayBux3/UkASY0d48ciT4LdLdZi/ktE8rkhdipwTqArdT+sV1ybFom9K8EVC3+Oddw3G/sSYI+Zv/7h/pXO4k+LFIaF753HdfD5zB/S9e/3rB575fHPWpmgqRt9xbpBM1YQae9iXRhdc9ggXq/0WRcTT3tL67FHTB6lQaEB2ES8Fiyn07n/Uh3Kf5k8xjrXAXfwQ4j29w6GfKJtbYC6oC4K19spf4jIkCazP1zJBEWjMkShzF/jdBD3Bybg3OCl/hH4nIu6m+YzAXyyTwZhk5Onf6Ek4zAqjq8tkIeICDPgXZyxddyAoZwHb3X6yJjYillBgqTRGfBq/qweW8z15Woertr0m6Lo0CRd/q7L7A7+qe+c=
// https://github.com/vuejs/rfcs/discussions/436#discussioncomment-6317743
const RecordTable = RecordTableGeneric<Tax, FinancialAccount[], 'tax_id'>;
const ModelWithInput = ModelWithInputGeneric<number, Tax[]>;
const ConfirmActionModel = ConfirmActionModelGeneric<Tax>;
const InputTemplateModel = InputTemplateModelGeneric<AddNewTaxModelExtraData, typeof AddTaxModelInitialValue>;

const AddTaxModelInitialValue: AddTaxModelInitialValueType = {}

const { getFinanctialAccounts } = useFinancialAccounts()
const { batch, updateTax: updateTaxBatch, removeTax: removeTaxBatch } = useBatchFetch(useTaxes)
const { getTaxes, addTax, removeTax, updateTax } = useTaxes()

const loadTaxes = () => getTaxes();
const fetchAccounts = async () => await getFinanctialAccounts().catch(() => [])

// References to template elements
// https://github.com/vuejs/core/issues/8373
const recordTableRef = ref<GenericComponentInstance<typeof RecordTable> | null>(null);
const modelWithInputRef = ref<GenericComponentInstance<typeof ModelWithInput> | null>(null);
const confirmActionModelRef = ref<GenericComponentInstance<typeof ConfirmActionModel> | null>(null);
const addTaxModelRef = ref<GenericComponentInstance<typeof InputTemplateModel> | null>(null);

const sorter = computed(() => (a: ModifiedTax, b: ModifiedTax) =>
    a.tax_id > b.tax_id ? 1 : -1
)

/**
* Handles commands emited from the dropdown menu of a row
* @param command Emitted command
* @param record Record on which it was called
*/
const handleCommand = async (command: string, record: ModifiedTax) => {
    switch (command) {
        case "delete":
            await handleDeleteTax(record);
            break;
        case "edit":
            await handleEditTax(record);
            break;
        default:
            break;
    }
}

const handleDeleteTax = async (record: ModifiedTax) => {
    if (confirmActionModelRef.value == undefined) return;

    const { showLoading, finish, start } = confirmActionModelRef.value?.setup(
        "Delete Tax type",
        `You're about to delete ${record.name} (${record.tax_id}) tax type. This action is irreversible!`,
        "Delete",
        "Cancel"
    )

    const confirm = await start().then(response => response.action == "confirm").catch(() => false);

    if (!confirm) return;

    showLoading(`Deleting tax type`)

    const doActions = async () => {
        // Update the account on the database
        if (await removeTax(record.tax_id).then(() => true).catch(() => false)) {
            recordTableRef.value?.removeRecord(record.tax_id);
        }
    }

    await doActions().catch(console.log).finally(() => finish());
}

const handleAddTax = async () => {
    if (addTaxModelRef.value == undefined) return;

    const accounts = await loadTaxes();

    const { finish, showLoading, start } = addTaxModelRef.value.setup("Add new Tax type", "Add Tax Type", "Cancel", {
        existingTaxNames: accounts.map(account => account.name)
    });

    const { action, data } = await start().catch(() => ({ action: "cancel", data: undefined }));

    // There is really no need to check whether data is undefined 
    // This is solely for the purpose of telling Typescript compiler that data is not undefined below this point
    if (action == "cancel" || !data) return;

    showLoading("Creating new Tax type");

    const newTax: Omit<Tax, "tax_id"> = {
        description: data.description,
        name: data.taxName,
        tax_rate: new Decimal(data.taxRate)
    }

    const doActions = async () => {
        const newTaxId = await addTax(newTax);
        recordTableRef.value?.addRecord({ ...newTax, tax_id: newTaxId })
    }

    await doActions().catch(console.log).finally(() => finish());
}

const handleEditTax = async (record: ModifiedTax) => {
    // We'll be using the same model used to adding an account
    if (addTaxModelRef.value == undefined) return;

    const { finish, showLoading, start } = addTaxModelRef.value.setup(`Edit ${record.name}`, "Save changes", "Cancel", {
        existingTaxNames: (await loadTaxes()).map(tax => tax.name),
    }, record);

    const { action, data } = await start().catch(() => ({ action: "cancel", data: undefined }));

    // There is really no need to check whether data is undefined 
    // This is solely for the purpose of telling Typescript compiler that data is not undefined below this point
    if (action == "cancel" || !data) return;

    showLoading("Saving changes");

    const updatedTax = {
        ...data,
        tax_id: record.tax_id
    }

    const doActions = async () => {
        const newAccountId = await updateTax(updatedTax);
        recordTableRef.value?.updateRecord({ ...record, ...data })
    }

    await doActions().catch(console.log).finally(() => finish());
}

const handleDeleteSelectedTaxTypes = async (records: (ModifiedTax)[]) => {
    if (confirmActionModelRef.value == undefined) return;

    const { showLoading, finish, start } = confirmActionModelRef.value?.setup(
        `Delete ${records.length} tax types?`,
        "Delete",
        "Cancel"
    )

    const confirm = await start().then(response => response.action == "confirm").catch(() => false);

    if (!confirm) return;

    showLoading(`Deleting ${records.length} tax types`)

    const doActions = async () => {
        // Delete the accounts on the database
        if (await batch<void[]>(() => Promise.all(records.map(record => removeTaxBatch(record.tax_id))), `Delete ${records.length} tax types`, true).then(() => true).catch(() => false)) {
            records.forEach(record => recordTableRef.value?.removeRecord(record.tax_id))
        }
    }

    await doActions().catch(console.log).finally(() => finish());
}

const accountsUsingTaxType = (taxId: number, accounts: FinancialAccount[]) => accounts.filter(account => account.tax_id == taxId).length

const addTaxValidator = ({ taxName, taxRate }: typeof AddTaxModelInitialValue, { existingTaxNames }: AddNewTaxModelExtraData) => ({
    taxName: [
        !taxName ? "A name has to be provided" : "",
        taxName == "" ? "Name cannot be empty" : "",
        existingTaxNames.map(s => s.toLowerCase()).includes(taxName?.toLowerCase() ?? "") ? "A Tax type with the given name already exists" : ""
    ].find(x => x != ""),

    taxRate: [
        !taxRate ? "A rate has to be provided" : "",
        taxRate == "" ? "Rate cannot be empty" : "",
        isProperNumberString(taxRate) ? "Invalid tax rate" : ""
    ].find(x => x != ""),

    description: undefined, // Optional
})
</script>

<style scoped>
.header-buttons-leave-active {
    transition: all 0.3s ease;
}

.header-buttons-enter-active {
    transition: all 0.3s ease;
}

.header-buttons-enter-from,
.header-buttons-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}
</style>