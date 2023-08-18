// @ts-check

import { useRefHistory, useMagicKeys, whenever } from "@vueuse/core";

/**
 * Enable Undoing and Redoing on the given Ref
 * @param {import("vue").Ref<any>} ref 
 */
export const useUndoRedo = (ref) => {
    const { undo, redo } = useRefHistory(ref)

    const { Ctrl_Z, Ctrl_Y } = useMagicKeys();

    whenever(Ctrl_Z, () => undo())
    whenever(Ctrl_Y, () => redo())
}