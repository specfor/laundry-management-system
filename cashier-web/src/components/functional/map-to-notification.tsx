import type { ArrayElement } from "@/types/util";
import type { FunctionalComponent, Ref } from "vue";
import { UseTimeAgo } from "@vueuse/components";

type NormalNotificationData = {
    type: "success" | "error"
    createdAt: Date
    props: {
        origin: string
        status: number
        statusText: string
    }
    pinned?: boolean
}

type ProgressNotificationData = {
    type: "progress"
    createdAt: Date
    props: {
        total: Ref<number>
        completed: Ref<number>
    }
    pinned?: boolean
}

export const mapToComponent = ({ createdAt, props, type }: ArrayElement<(NormalNotificationData | ProgressNotificationData)[]>) => {
    if (type == "error" || type == "success") {
        const Notif: FunctionalComponent = () => {
            return (
                <tr>
                    <td>
                        <FNotificationIcon type={type} />
                    </td>
                    <td class="text-sm text-ellipsis">
                        <span class="font-semibold">{props.origin} ({
                            props.status
                        }) </span>
                        <span class="block text-sm">{props.statusText}</span>
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
    } else {
        const progressProps = props as ProgressNotificationData['props']
        const Notif: FunctionalComponent = () => {
            return (
                <tr>
                    <td>
                        Progress
                    </td>
                    <td class="text-sm text-ellipsis">
                        {progressProps.completed.value} / {progressProps.total.value}
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
}

const FNotificationIcon: FunctionalComponent<{ type: "error" | "success" }> = (
    { type }
) => {
    switch (type) {
        case "success":
            return (
                <icon-mdi-check-circle-outline height={25} width={25} style="color: hsl(var(--a))" />
            )
        default:
            return (
                <icon-mdi-close-circle-outline height={25} width={25} style="color: hsl(var(--a))" />
            )
    }
}