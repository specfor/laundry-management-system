/**
 * @vitest-environement happy-dom
 */

// @ts-check
import { flushPromises, mount } from "@vue/test-utils";
import LedgerRecordEntryTable from "../../src/components/LedgerRecordEntryTable.vue";
import { setupServer } from 'msw/node'
import { rest } from 'msw'
import { NotificationInjectionKey } from "../../src/util/injection-symbols";
import { defineComponent, provide } from "vue";
import { ElSelect } from "element-plus";

const server = setupServer(
    rest.get('/api/v1/taxes', (req, res, ctx) => {
        return res(ctx.status(200), ctx.json(/** @type {import("../../src/types").RawFinancialAccount[]}*/([
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
        ])))
    }),
    rest.get('/v1/financial-accounts', (req, res, ctx) => {
        return res(ctx.status(200), ctx.json(/** @type {import("../../src/types").RawTax[]} */([
            {
                tax_id: 1,
                deleted: 0,
                locked: 0,
                name: "Test tax",
                tax_rate: "1.1",
                description: "This is a test tax"
            }
        ])))
    }),
)

describe("Testing Ledger Record entry table", () => {
    // Start server before all tests
    beforeAll(() => server.listen({ onUnhandledRequest: 'error' }))
    //  Close server after all tests
    afterAll(() => server.close())
    // Reset handlers after each test `important for test isolation`
    afterEach(() => server.resetHandlers())

    test.skip("Ledger Record entry table should render", async () => {
        const TestComponent = defineComponent({
            components: {
                'LedgerRecordEntryTable': LedgerRecordEntryTable
            },
            template: '<Suspense><LedgerRecordEntryTable :initialRows="1"/></Suspense>',
            setup() {
                provide(NotificationInjectionKey, {
                    showError: () => { },
                    showSuccess: () => { }
                })
            }
        })

        const wrapper = mount(TestComponent, {
            global: {
                stubs: {
                    teleport: true
                }
            }
        })

        await flushPromises();
        const selects = wrapper.findAllComponents(ElSelect)
        selects.forEach(el => console.log(el.html()))
        console.log(selects.length)
        expect(selects[0].html()).toContain("Test Account")
        expect(selects[1].html()).toContain("Test tax")
        // expect(wrapper.findAll(`tr[class="entry-row"]`)).toHaveLength(1);
        // expect(wrapper.html()).toContain("This is a test description")
    })
})