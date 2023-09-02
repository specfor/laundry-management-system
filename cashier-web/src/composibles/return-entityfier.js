// @ts-check

/**
 * @template Entity
 * @template {keyof Entity} IDKey
 * 
 * @param {(...args: [...import("ts-toolbelt").Object.ListOf<Omit<Entity, IDKey>>]) => Promise<Entity[IDKey]>} method
 * @param {IDKey} idKey
 */
export const useReturnEntityfier = (method, idKey) => {
    /**
     * @param  {import("ts-toolbelt").Object.ListOf<Omit<Entity, IDKey>>} args 
     * @returns {Promise<Entity>}
     */
    const newMethod = (...args) => new Promise((resolve, reject) => {
        method(...args)
            .then(data => resolve({
                ...(Object.keys()),
                [idKey]: data
            }))
    })
}