import { useAuthorizedFetch } from "./fetch"

export function useOrders() {
    const addOrder = async (data) => {
        const { data: res, error } = await useAuthorizedFetch('orders/add').json().post(data);
    }
    let response = await sendJsonPostRequest(apiBaseUrl + "/orders/add", {
        "items": items,
        "customer-id": data['customer'], // From AddNewModel
        "customer-comments": order.data['comment']
    })

    if (response.status === "success") {
        getOrders()
        pushSuccessNotification('Add New Payment', response.message)
    } else {
        pushErrorNotification('Add New Payment', response.message)
    }
}