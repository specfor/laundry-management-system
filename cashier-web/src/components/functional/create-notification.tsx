import type { FunctionalComponent } from "vue";
import { UseTimeAgo } from "@vueuse/components";
import CheckCircleOutline from '~icons/mdi/check-circle-outline'
import CloseCirleOutline from '~icons/mdi/close-circle-outline'

type NotificationData = {
    type: "success" | "error"
    createdAt: Date
    props: {
        origin: string
        status: number
        statusText: string
    }
}

export const createNotificationComponent = ({ createdAt, type, props: { origin, status, statusText } }: NotificationData) => {
    const Notif: FunctionalComponent = () => {
        return (
            <tr>
                <td>
                    <FNotificationIcon type={type} />
                </td>
                <td class="text-sm text-ellipsis">
                    <span class="font-semibold">{origin} ({
                        status
                    }) </span>
                    <span class="block text-sm">{statusText}</span>
                </td>
                <UseTimeAgo time={createdAt}>
                    {({ timeAgo }: { timeAgo: string }) => (
                        <td class="text-slate-500">{timeAgo}</td>
                    )}
                </UseTimeAgo>
            </tr>
        )
    }

    return Notif;
}

const FNotificationIcon: FunctionalComponent<{ type: "error" | "success" }> = (
    { type }
) => {
    switch (type) {
        case "success":
            return (
                <CheckCircleOutline class="h-6 w-6 text-success"/>
            )
        default:
            return (
                <CloseCirleOutline class="h-6 w-6 text-error"/>
            )
    }
}