import { useDark as useDarkRaw } from '@vueuse/core'

export const useDark = () => {
    const isDark = useDarkRaw({
        onChanged: (dark: boolean) => {
            document.querySelector('html')?.setAttribute('data-theme', dark ? 'dark' : 'corporate');
            dark ? document.querySelector('html')?.classList.add('dark') : document.querySelector('html')?.classList.remove('dark');
        },
        initialValue: 'light',
        valueDark: 'dark',
        valueLight: 'light'
    })

    return isDark;
}