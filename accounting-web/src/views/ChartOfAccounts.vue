<template>
    <!-- Add new Account Model -->
    <InputTemplateModel ref="addAccountModelRef" :initial-value="AddAccountModelInitialValue" :validator="addAccountValidator">
        <template #inputs="{ data, errors, setValue, mergeValue, value }">
            <TextInput label="Account Name" :error="errors.accountName" :modelValue="value.accountName"
                @update:modelValue="setValue({ ...value, accountName: $event })"></TextInput>

            <div class="flex flex-row gap-5">
                <div class="form-control w-full max-w-xs">
                    <label class="label">
                        <span class="label-text">Account Type</span>
                    </label>
                    <el-tree-select :modelValue="value.accountType" @update:modelValue="mergeValue($event, 'accountType')"
                        :data="treeSelectDataForAddAccount" :render-after-expand="true"
                        :filter-node-method="filterNodeMethod" filterable />
                    <label class="label">
                        <span class="label-text-alt"></span>
                        <span v-if="errors.accountType" class="label-text-alt">{{ errors.accountType }}</span>
                    </label>
                </div>

                <div class="form-control w-full max-w-xs">
                    <label class="label">
                        <span class="label-text">Tax Rate</span>
                    </label>
                    <el-select class="h-full border-none" :class="{ 'border-red-600 border-2': errors.taxId }"
                        :modelValue="value.taxId" @update:modelValue="setValue({ ...value, taxId: $event })" filterable
                        placeholder="Tax Type">
                        <el-option v-for="item in data?.taxes" :key="item.tax_id" :label="item.name" :value="item.tax_id">
                            <span class="float-left mr-2">{{ item.name }}</span>
                            <span class="text-gray-400 float-right">
                                {{ toReadable(item.tax_rate) + "%" }}
                            </span>
                        </el-option>
                        <template #empty>
                            <span>No Results ...</span>
                        </template>
                    </el-select>
                    <label class="label">
                        <span class="label-text-alt"></span>
                        <span v-if="errors.taxId" class="label-text-alt">{{ errors.taxId }}</span>
                    </label>
                </div>
            </div>

            <TextInput label="Account Code" :error="errors.accountCode" :modelValue="value.accountCode"
                @update:modelValue="setValue({ ...value, accountCode: $event })"></TextInput>

            <div class="form-control w-full max-w-xs m-0">
                <label class="label">
                    <span class="label-text">Account Description</span>
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
        <RecordTable ref="recordTableRef" :command-handler="handleCommand" :get-records="loadAccounts"
            :column-slots="['name', 'type', 'tax']" :search-fields="['name', 'description']" :filter="filter"
            :sorter="sorter" :extra-data-fetcher="fetchTaxes" :id-property="'account_id'">

            <template #table-headers>
                <th>Name</th>
                <th>Type</th>
                <th>Tax Rate</th>
            </template>

            <template #row-actions="{ record }">
                <el-dropdown-item :icon="Delete" command="delete">Delete</el-dropdown-item>
                <el-dropdown-item :icon="TakeawayBox" command="archive">Archive</el-dropdown-item>
                <el-dropdown-item :icon="Edit" command="edit">Edit</el-dropdown-item>
                <el-dropdown-item :icon="EditPen" command="edit-tax-rate">Change Tax Rate</el-dropdown-item>
            </template>

            <template #header-actions="{ selectedRecords }">
                <TransitionGroup name="header-buttons">
                    <div class="tooltip" v-show="selectedRecords.length > 0" data-tip="Delete Selected Accounts"
                        key="del-but">
                        <button :disabled="selectedRecords.length == 0" @click="console.log(selectedRecords)"
                            class="btn btn-square btn-error btn-sm">
                            <svg class="w-4 h-4 text-gray-800 dark:text-white stroke-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M1 5h16M7 8v8m4-8v8M7 1h4a1 1 0 0 1 1 1v3H6V2a1 1 0 0 1 1-1ZM3 5h12v13a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5Z" />
                            </svg>
                        </button>
                    </div>
                    <div class="tooltip" v-show="selectedRecords.length > 0" key="tax-but"
                        data-tip="Change the tax rate of Selected Accounts">
                        <button :disabled="selectedRecords.length == 0"
                            @click="handleUpdateTaxTypeOfSelectedAccounts(selectedRecords)"
                            class="btn btn-square btn-accent btn-sm text-xl text-white">
                            %
                        </button>
                    </div>
                    <button class="btn btn-sm btn-neutral" key="add-acc-but" @click="handleAddAccount">
                        <svg class="w-4 h-4 text-gray-800 dark:text-white stroke-white" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16" />
                        </svg>
                        New Account
                    </button>
                </TransitionGroup>
            </template>

            <template #filter-content="{ data }">
                <el-popover :width="300" placement="bottom" :visible="filterPopoverVisible"
                    popper-style="box-shadow: rgb(14 18 22 / 35%) 0px 10px 38px -10px, rgb(14 18 22 / 20%) 0px 10px 20px -15px;">
                    <template #reference>
                        <button class="btn btn-sm btn-neutral btn-square group"
                            @click="filterPopoverVisible = !filterPopoverVisible">
                            <svg class="w-[17px] h-[17px] text-gray-800 dark:text-white stroke-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 18">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m2.133 2.6 5.856 6.9L8 14l4 3 .011-7.5 5.856-6.9a1 1 0 0 0-.804-1.6H2.937a1 1 0 0 0-.804 1.6Z" />
                            </svg>
                        </button>
                    </template>
                    <template #default>
                        <div class="w-full flex flex-row justify-between">
                            <h2 class="text-lg font-medium">Filter Accounts</h2>
                            <svg @click="filterPopoverVisible = false" xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div class="form-control w-full max-w-xs">
                            <label class="label">
                                <span class="label-text">Account Type</span>
                            </label>
                            <el-tree-select v-model="selectedAccountTypesRaw" :data="treeSelectData" multiple
                                :render-after-expand="true" show-checkbox :filter-node-method="filterNodeMethod"
                                filterable />
                        </div>
                        <div class="form-control w-full max-w-xs">
                            <label class="label">
                                <span class="label-text">Tax Rate</span>
                            </label>
                            <el-select class="h-full border-none" filterable placeholder="Tax Type" v-model="selectedTaxId">
                                <el-option v-for="item in data ?? []" :key="item.tax_id" :label="item.name"
                                    :value="item.tax_id">
                                    <span class="float-left mr-2">{{ item.name }}</span>
                                    <span class="text-gray-400 float-right">
                                        {{ toReadable(item.tax_rate) + "%" }}
                                    </span>
                                </el-option>
                                <template #empty>
                                    <span>No Results ...</span>
                                </template>
                            </el-select>
                        </div>
                        <div class="flex flex-row justify-between mt-2">
                            <!-- <button class="btn btn-primary btn-sm">Apply Filters</button> -->
                            <button class="btn btn-sm" @click="clearFilters">Clear Filters</button>
                        </div>
                    </template>
                </el-popover>
            </template>

            <template #tax="{ record, extra }">
                <span class="text-base block">
                    {{ toTaxType(record.tax_id, extra)?.name }}
                </span>
                <span class="text-sm text-slate-700">
                    {{ toReadable(toTaxType(record.tax_id, extra)?.tax_rate ?? new Decimal(0)) }}%
                </span>
            </template>

            <template #name="{ record }">
                <span class="text-base font-medium">
                    {{ record.name }} - {{ record.account_id }}
                    <div class="badge badge-warning gap-2" v-if="record.archived == 1">
                        <svg class="w-3 h-3 stroke-current" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                                d="M8 8v1h4V8m4 7H4a1 1 0 0 1-1-1V5h14v9a1 1 0 0 1-1 1ZM2 1h16a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1Z" />
                        </svg>
                        Archived
                    </div>
                </span>
                <p class="text-ellipsis">{{ record.description.length > 100 ? record.description.slice(0, 100)
                    +
                    "..." : record.description }} </p>
            </template>

            <template #type="{ record }">
                {{ record.type }}
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
import { toReadable } from '../util/decimal-util';
import { Decimal } from 'decimal.js'
import { computed, ref, watch } from 'vue';
import type { FinancialAccount, GenericComponentInstance, Tax } from '../types';
import { useBatchFetch } from '../composibles/batch-fetch';
import { accountTypesTree } from '../util/account-types';
import Fuse from 'fuse.js';
import TextInput from '../components/inputs/TextInput.vue';
import { useRouteQuery } from '@vueuse/router'

// Type Inside the record table with some extra data
type ModifiedFinancialAccount = FinancialAccount & ArrangementData;
type AddNewAccountModelExtraData = { taxes: Tax[], existingAccountNames: string[], existingAccountCodes: string[] };
interface AddAccountModelInitialValueType {
    accountName?: string,
    accountCode?: string,
    description?: string,
    taxId?: number,
    accountType?: string
}

// Defining generics of above imported generic elements
// https://play.vuejs.org/#eNp9U8Fu2zAM/RVCl3RAER96C5wAWxEM22ErstyiHDybTtTakiDKTQLD/z5KTmN72HKxRfGR4iMfW/HZ2vl7g2IhUsqdsh4IfWOhyvRhKYUnKVZSq9oa56EFOmZVZU4bLKGD0pkaZhw9k/qG+YoancqfTW17gBTzJFjhGSkiUnt0ZZYjPDfkTb3B3LgCWqkBVLEA8k7pQ7B0VuNgdyE4N5o8KI81LIeQmSpmj0PELPzCRQeUeUWlQpo8Ns1EnGpglo6Bu/3qYRcw+09DTO+KFJdjwpNIblua9D3lIxucxFaZR7YA0lGORayBux3/UkASY0d48ciT4LdLdZi/ktE8rkhdipwTqArdT+sV1ybFom9K8EVC3+Oddw3G/sSYI+Zv/7h/pXO4k+LFIaF753HdfD5zB/S9e/3rB575fHPWpmgqRt9xbpBM1YQae9iXRhdc9ggXq/0WRcTT3tL67FHTB6lQaEB2ES8Fiyn07n/Uh3Kf5k8xjrXAXfwQ4j29w6GfKJtbYC6oC4K19spf4jIkCazP1zJBEWjMkShzF/jdBD3Bybg3OCl/hH4nIu6m+YzAXyyTwZhk5Onf6Ek4zAqjq8tkIeICDPgXZyxddyAoZwHb3X6yJjYillBgqTRGfBq/qweW8z15Woertr0m6Lo0CRd/q7L7A7+qe+c=
// https://github.com/vuejs/rfcs/discussions/436#discussioncomment-6317743
const RecordTable = RecordTableGeneric<FinancialAccount, Tax[], 'account_id'>;
const ModelWithInput = ModelWithInputGeneric<number, Tax[]>;
const ConfirmActionModel = ConfirmActionModelGeneric<FinancialAccount>;
const InputTemplateModel = InputTemplateModelGeneric<AddNewAccountModelExtraData, typeof AddAccountModelInitialValue>;

const AddAccountModelInitialValue: AddAccountModelInitialValueType = {}

const treeSelectData = [...accountTypesTree(), { label: 'Archived', value: '["Archived"]' }]
/**
 * Converts the value property of nodes which is JSON Array string with one element, into a string
 */
const treeSelectDataForAddAccount = [...accountTypesTree().map(({ children, ...rest }) => ({ ...rest, children: children.map(({ label, value }) => ({ label, value: (JSON.parse(value) as string[])[0] })) }))]

const { getFinanctialAccounts, updateFinancialAccount, removeFinancialAccount, addFinancialAccount } = useFinancialAccounts()
const { batch, updateFinancialAccount: updateFinancialAccountBatch } = useBatchFetch(useFinancialAccounts)
const { getTaxes } = useTaxes()

const loadAccounts = () => getFinanctialAccounts();
const fetchTaxes = async () => await getTaxes().catch(() => [])

// References to template elements
// https://github.com/vuejs/core/issues/8373
const recordTableRef = ref<GenericComponentInstance<typeof RecordTable> | null>(null);
const modelWithInputRef = ref<GenericComponentInstance<typeof ModelWithInput> | null>(null);
const confirmActionModelRef = ref<GenericComponentInstance<typeof ConfirmActionModel> | null>(null);
const addAccountModelRef = ref<GenericComponentInstance<typeof InputTemplateModel> | null>(null);

const filterPopoverVisible = ref<boolean>(false);
const selectedAccountTypesRaw = ref<string[]>([]);

// Get URL Query parameters
const taxId = useRouteQuery('tax-id', '-1', { transform: Number })
const selectedTaxId = ref(taxId.value == -1 ? undefined : taxId)

watch(selectedTaxId, (value) => taxId.value = value ?? -1);

const selectedAccountTypes = computed(() => selectedAccountTypesRaw.value.map(s => JSON.parse(s) as string[]).flat().filter((value, index, array) => array.indexOf(value) === index))

const filter = computed(() => (account: ModifiedFinancialAccount) => [
    selectedAccountTypes.value.includes("Archived") ? account.archived == 1 : true,
    selectedAccountTypes.value.length > 0 ? selectedAccountTypes.value.includes(account.type) : true,
    selectedTaxId.value ? account.tax_id == selectedTaxId.value : true
].every(x => x))

const sorter = computed(() => (a: ModifiedFinancialAccount, b: ModifiedFinancialAccount) =>
    a.account_id > b.account_id ? 1 : -1
)

/**
* Handles commands emited from the dropdown menu of a row
* @param command Emitted command
* @param record Record on which it was called
*/
const handleCommand = async (command: string, record: ModifiedFinancialAccount) => {
    switch (command) {
        case "delete":
            await handleDeleteAccount(record);
            break;
        case "archive":
            await handleArchiveAccount(record);
            break;
        case "edit-tax-rate":
            await handleEditTaxType(record);
            break;
        case "edit":
            await handleEditAccount(record);
            break;
        default:
            break;
    }
}

const handleEditTaxType = async (record: ModifiedFinancialAccount) => {
    if (modelWithInputRef.value == undefined) return;

    const { showLoading, finish, start } = modelWithInputRef.value?.setup(
        `Change the tax type of ${record.name} (${record.account_id})`,
        "Save",
        "Cancel",
        await fetchTaxes()
    )

    const newTaxId = await start().then(response => response.data).catch(() => undefined) ?? -1;

    if (newTaxId == -1) return;

    showLoading(`Saving changes`)

    const doActions = async () => {
        // Update the account on the database
        if (await updateFinancialAccount({
            ...record,
            tax_id: newTaxId
        }).then(() => true).catch(() => false)) {
            newTaxId != -1 ? recordTableRef.value?.updateRecord({ ...record, tax_id: newTaxId }) : null;
        }
    }

    await doActions().catch(console.log).finally(() => finish());
}

const handleDeleteAccount = async (record: ModifiedFinancialAccount) => {
    if (confirmActionModelRef.value == undefined) return;

    const { showLoading, finish, start } = confirmActionModelRef.value?.setup(
        "Delete Account",
        `You're about to delete ${record.name} (${record.account_id}) account. This action is irreversible!`,
        "Delete",
        "Cancel"
    )

    const confirm = await start().then(response => response.action == "confirm").catch(() => false);

    if (!confirm) return;

    showLoading(`Deleting account`)

    const doActions = async () => {
        // Update the account on the database
        if (await removeFinancialAccount(record.account_id).then(() => true).catch(() => false)) {
            recordTableRef.value?.removeRecord(record.account_id);
        }
    }

    await doActions().catch(console.log).finally(() => finish());
}

const handleArchiveAccount = async (record: ModifiedFinancialAccount) => {
    if (confirmActionModelRef.value == undefined) return;

    const { showLoading, finish, start } = confirmActionModelRef.value?.setup(
        `Archive ${record.name} (${record.account_id}) account.`,
        "Archive",
        "Cancel"
    )

    const confirm = await start().then(response => response.action == "confirm").catch(() => false);

    if (!confirm) return;

    showLoading(`Saving changes`)

    const doActions = async () => {
        // Update the account on the database

        // TODO: Archive the account
        // if (await updateFinancialAccount({
        //     ...record,
        //     tax_id: newTaxId
        // }).then(() => true).catch(() => false)) {
        //     newTaxId != -1 ?  : null;
        // }

        recordTableRef.value?.updateRecord({ ...record, archived: 1 });
    }

    await doActions().catch(console.log).finally(() => finish());
}

const handleAddAccount = async () => {
    if (addAccountModelRef.value == undefined) return;

    const accounts = await loadAccounts();

    const { finish, showLoading, start } = addAccountModelRef.value.setup("Add new Account", "Add Account", "Cancel", {
        taxes: await fetchTaxes(),
        existingAccountNames: accounts.map(account => account.name),
        existingAccountCodes: accounts.map(account => account.code)
    });

    const { action, data } = await start().catch(() => ({ action: "cancel", data: undefined }));

    // There is really no need to check whether data is undefined 
    // This is solely for the purpose of telling Typescript compiler that data is not undefined below this point
    if (action == "cancel" || !data) return;

    showLoading("Creating new Account");

    const newAccount: Omit<FinancialAccount, "account_id"> = {
        archived: 0, // These 2 fields will get discarded by the Backend anyway 
        code: data.accountCode,
        deletable: 0, // These 2 fields will get discarded by the Backend anyway
        description: data.description,
        name: data.accountName,
        tax_id: data.taxId,
        type: data.accountType
    }

    const doActions = async () => {
        const newAccountId = await addFinancialAccount(newAccount);
        recordTableRef.value?.addRecord({ ...newAccount, account_id: newAccountId })
    }

    await doActions().catch(console.log).finally(() => finish());
}

const handleEditAccount = async (record: ModifiedFinancialAccount) => {
    // We'll be using the same model used to adding an account
    if (addAccountModelRef.value == undefined) return;

    const accounts = await loadAccounts();

    const { finish, showLoading, start } = addAccountModelRef.value.setup(`Edit ${record.name}`, "Save changes", "Cancel", {
        taxes: await fetchTaxes(),
        existingAccountNames: accounts.map(account => account.name),
        existingAccountCodes: accounts.map(account => account.code)
    }, record);

    const { action, data } = await start().catch(() => ({ action: "cancel", data: undefined }));

    // There is really no need to check whether data is undefined 
    // This is solely for the purpose of telling Typescript compiler that data is not undefined below this point
    if (action == "cancel" || !data) return;

    showLoading("Saving changes");

    const updatedAccount = {
        ...data,
        account_id: record.account_id
    }

    const doActions = async () => {
        const newAccountId = await updateFinancialAccount(updatedAccount);
        recordTableRef.value?.updateRecord({ ...record, ...data })
    }

    await doActions().catch(console.log).finally(() => finish());
}

const handleUpdateTaxTypeOfSelectedAccounts = async (records: (ModifiedFinancialAccount)[]) => {
    if (modelWithInputRef.value == undefined) return;

    const { showLoading, finish, start } = modelWithInputRef.value?.setup(
        `Change the tax type of ${records.length} accounts`,
        "Save",
        "Cancel",
        await fetchTaxes()
    )

    const newTaxId = await start().then(response => response.data).catch(() => undefined) ?? -1;

    if (newTaxId == -1) return;

    showLoading(`Saving changes`)

    const doActions = async () => {
        // Update the account on the database

        if (await batch<void[]>(() => Promise.all(records.map(record => updateFinancialAccountBatch({
            ...record,
            tax_id: newTaxId
        }))), `Update tax type of ${records.length} accounts`, true).then(() => true).catch(() => false)) {
            records.forEach(record => recordTableRef.value?.updateRecord({ ...record, tax_id: newTaxId }))
        }
    }

    await doActions().catch(console.log).finally(() => finish());
}

/** 
 * Gets the tax type
 */
const toTaxType = (taxId: number, taxes: Tax[] | null) => taxes?.find(tax => tax.tax_id == taxId) ?? undefined

// A helper type to get the element type of an array
type ArrayElement<ArrayType extends readonly unknown[]> = ArrayType extends readonly (infer ElementType)[] ? ElementType : never;

const filterNodeMethod = (value: string, data: ArrayElement<ReturnType<typeof accountTypesTree>>) => {
    if (value == "") return true;
    // Just a shitty way to search how matching 2 strings are, but I guess we can roll with iit.
    return new Fuse([data], { keys: ["label"] }).search(value).length > 0;
}

const addAccountValidator = ({ accountName, accountType, taxId, accountCode }: typeof AddAccountModelInitialValue, { existingAccountNames, existingAccountCodes }: AddNewAccountModelExtraData) => ({
    accountName: [
        !accountName ? "An account name has to be provided" : "",
        accountName == "" ? "Account name cannot be empty" : "",
        existingAccountNames.map(s => s.toLowerCase()).includes(accountName?.toLowerCase() ?? "") ? "Account with the given name already exists" : ""
    ].find(x => x != ""),

    accountType: [
        !accountType ? "Account type has to be selected" : "",
    ].find(x => x != ""),

    taxId: [
        !taxId ? "Tax rate has to be selected" : "",
    ].find(x => x != ""),

    accountCode: [
        !accountCode ? "An account code has to be provided" : "",
        accountCode == "" ? "Account code cannot be empty" : "",
        existingAccountCodes.includes(accountCode ?? "") ? "Account with the given code already exists" : ""
    ].find(x => x != ""),

    description: undefined, // Optional
})

const clearFilters = () => {
    selectedAccountTypesRaw.value = [];
    selectedTaxId.value = undefined;
}
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