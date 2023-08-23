import { mount } from "@vue/test-utils";
import LedgerRecordEntryTable from "../../src/components/LedgerRecordEntryTable.vue";
import { setupServer } from 'msw/node'
import { rest } from 'msw'
import type { RawTax, RawFinancialAccount } from "../../src/types";
import { NotificationInjectionKey } from "../../src/util/injection-symbols";
import { defineComponent, type Component, provide } from "vue";


const restHandlers = [
    rest.get('http://laundry-api.localhost/api/v1/taxes', (req, res, ctx) => {
        return res(ctx.status(200), ctx.json([
            {
                account_id: 1,
                archived: 0,
                code: "AA",
                deletable: 1,
                locked: 0,
                name: "Test Account",
                tax_id: 1,
                type: "Revenue",
                description: "This is a test description"
            }
        ] as RawFinancialAccount[]))
    }),
    rest.get('http://laundry-api.localhost/api/v1/financial-accounts', (req, res, ctx) => {
        return res(ctx.status(200), ctx.json([
            {
                tax_id: 1,
                deleted: 0,
                locked: 0,
                name: "Test tax",
                tax_rate: "1.1",
                description: "This is a test tax"
            }
        ] as RawTax[]))
    }),
]

const server = setupServer(...restHandlers)

describe("Testing Ledger Record entry table", () => {
    // Start server before all tests
    beforeAll(() => server.listen({ onUnhandledRequest: 'error' }))
    //  Close server after all tests
    afterAll(() => server.close())
    // Reset handlers after each test `important for test isolation`
    afterEach(() => server.resetHandlers())

    test("Ledger Record entry table should render", async () => {
        const TestComponent = defineComponent({
            components: {
                'LedgerRecordEntryTable': LedgerRecordEntryTable as unknown as Component
            },
            template: '<Suspense><LedgerRecordEntryTable/></Suspense>',
            setup() {
                provide(NotificationInjectionKey, {
                    showError: () => {},
                    showSuccess: () => {}
                })
            }
        })

        mount(TestComponent, {
            global: {
                stubs: {
                    teleport: true
                }
            }
        })
    })
})