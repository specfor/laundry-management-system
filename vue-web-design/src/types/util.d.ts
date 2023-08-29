
export type NotificationInjection = {
    showSuccess: ({ origin, status, statusText }: {
        origin: string;
        status: number;
        statusText: string;
    }) => void;
    showError: ({ origin, status, statusText }: {
        origin: string;
        status: number;
        statusText: string;
    }) => void;
}

export as namespace Util;