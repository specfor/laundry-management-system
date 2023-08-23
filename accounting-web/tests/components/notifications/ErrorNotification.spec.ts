/**
 * @vitest-environement happy-dom
 */

import { mount } from "@vue/test-utils";
import ErrorNotification from "../../../src/components/notifications/ErrorNotification.vue";

describe("Testing Error notification", () => {
    test("Should render error notification", () => {
        mount(ErrorNotification, {
            props: {
                origin: "Test",
                status: 111,
                statusText: "Test status"
            }
        })
    })

    test("Should render header and message", () => {
        const wrapper = mount(ErrorNotification, {
            props: {
                origin: "Test",
                status: 111,
                statusText: "Test status"
            }
        })

        expect(wrapper.find(`h3[class="font-bold"]`).exists()).toBeTruthy()
        expect(wrapper.find(`h3[class="font-bold"]`).element.innerHTML).toEqual("Error occured on Test")
        
        expect(wrapper.find(`div[class="text-xs"]`).exists()).toBeTruthy()
        expect(wrapper.find(`div[class="text-xs"]`).element.innerHTML).toEqual("Test status (111)")
    })

    test("Should emit when close button is clicked", () => {
        const wrapper = mount(ErrorNotification, {
            props: {
                origin: "Test",
                status: 111,
                statusText: "Test status"
            }
        })

        wrapper.findAll(`svg`)[1].trigger('click')
        expect(wrapper.emitted('close')).toHaveLength(1);
    })
})