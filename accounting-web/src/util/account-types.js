// @ts-check

export const accountTypes = {
    Assets: ['Current Asset', 'Fixed Asset', 'Inventory', 'Non-Current Asset', 'Prepayment'],
    Equity: ['Equity'],
    Expenses: ['Depreciation', 'Direct Costs', 'Expense', 'Overhead'],
    Liabilities: ['Current Liability', 'Liability', 'Non-Current Liability'],
    Revenue: ['Other Income', 'Revenue', 'Sales']
}

export const accountTypesTree = () => {
    return Object.keys(accountTypes).map(key => ({
        label: key,
        value: JSON.stringify([accountTypes[/** @type {keyof accountTypes}*/(key)]]),
        children: [
            ...(accountTypes[/** @type {keyof accountTypes}*/(key)]).map(child => ({
                label: child,
                value: JSON.stringify([child])
            }))
        ]
    }))
}