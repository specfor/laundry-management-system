import { toValue } from "vue";
import { useAuthorizedFetch } from "./authorized-fetch"
import { useFetchResponseHandler } from "./fetch-error-handler";

export function useOrders() {
    const getOrders = async () => {
        const { data: res } = await useFetchResponseHandler(useAuthorizedFetch('orders').json().get(), 'Get Orders', false);
        // Data returned from the fetch is in a Ref<any>.
        // It is converted to a normal value and returned.
        return toValue(res).orders;
    }
    
    const addOrder = async (data) => {
        const { data: res } = await useFetchResponseHandler(useAuthorizedFetch('orders/add').json().post(data), 'Add Order', true);
        // Data returned from the fetch is in a Ref<any>.
        // It is converted to a normal value and returned.
        return toValue(res);
    }

    const editOrder = async (data) => {
        const { data: res } = await useFetchResponseHandler(useAuthorizedFetch('orders/update').json().post(data), 'Edit Order', true);
        // Data returned from the fetch is in a Ref<any>.
        // It is converted to a normal value and returned.
        return toValue(res);
    }

    const deleteOrder = async (data) => {
        const { data: res } = await useFetchResponseHandler(useAuthorizedFetch('orders/delete').json().post(data), 'Delete Order', true);
        // Data returned from the fetch is in a Ref<any>.
        // It is converted to a normal value and returned.
        return toValue(res);
    }
}