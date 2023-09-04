import { type InjectionKey } from "vue";

/** Used for Providing and Injecting the Notification System */
export const NotificationInjectionKey = Symbol() as InjectionKey<import("../types/util").NotificationInjection>

/** Used for Providing and Injecting Authentication data */
export const AuthInjectionKey = Symbol() as InjectionKey<import("../types/util").AuthInjection>