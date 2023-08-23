import { mount } from "@vue/test-utils"
import NotificationPopover from "../../../src/components/notifications/NotificationPopover.vue"
import { ElBadge, ElPopover } from "element-plus";

describe("Testing notification popover", () => {
    test("Should render", () => {
        const wrapper = mount(NotificationPopover, {
            props: {
                notifications: []
            }
        })
    })

    test("Should show the popover when hovered", () => {
        const wrapper = mount(NotificationPopover, {
            props: {
                notifications: []
            },
            global: {
                stubs: {
                    teleport: true
                }
            }
        })

        expect(wrapper.html()).toContain('Notifications')
    })

    test("Should show no notifications", async () => {
        const wrapper = mount(NotificationPopover, {
            props: {
                notifications: []
            },
            global: {
                stubs: {
                    teleport: true
                }
            }
        })
        
        expect(wrapper.html()).toContain('No Notifications')
    })

    test("Should render error notification", () => {
        const wrapper = mount(NotificationPopover, {
            props: {
                notifications: [
                    {
                        type: "error",
                        props: {
                            status: 111,
                            statusText: "test status",
                            origin: "test"
                        },
                        createdAt: new Date(Date.now())
                    }
                ]
            },
            global: {
                stubs: {
                    teleport: true
                }
            }
        })

        expect(wrapper.find(`span[class="font-semibold"]`).html()).toContain("test (111)")
        expect(wrapper.find(`td[class="text-slate-500"]`).html()).toContain("just now")
        expect(wrapper.find(`svg[class="shrink-0 h-6 w-6 stroke-error"]`).exists()).toBeTruthy()
    })

    test("Should render success notification", () => {
        const wrapper = mount(NotificationPopover, {
            props: {
                notifications: [
                    {
                        type: "success",
                        props: {
                            status: 111,
                            statusText: "test status",
                            origin: "test"
                        },
                        createdAt: new Date(Date.now())
                    }
                ]
            },
            global: {
                stubs: {
                    teleport: true
                }
            }
        })
        
        expect(wrapper.find(`span[class="font-semibold"]`).html()).toContain("test (111)")
        expect(wrapper.find(`td[class="text-slate-500"]`).html()).toContain("just now")
        expect(wrapper.find(`svg[class="shrink-0 h-6 w-6 stroke-success"]`).exists()).toBeTruthy()
    })
})