import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/View/Components/**/*.php',
    ],

    theme: {
        extend: {
            colors: {
                bg: {
                    primary: 'var(--color-bg-primary)',
                    secondary: 'var(--color-bg-secondary)',
                    tertiary: 'var(--color-bg-tertiary)',
                    elevated: 'var(--color-bg-elevated)',
                },
                border: {
                    subtle: 'var(--color-border-subtle)',
                    hover: 'var(--color-border-hover)',
                    active: 'var(--color-border-active)',
                },
                text: {
                    primary: 'var(--color-text-primary)',
                    secondary: 'var(--color-text-secondary)',
                    tertiary: 'var(--color-text-tertiary)',
                    muted: 'var(--color-text-muted)',
                },
                brand: {
                    primary: 'var(--color-brand-primary)',
                    hover: 'var(--color-brand-hover)',
                    success: 'var(--color-emerald)',
                    warning: 'var(--color-amber)',
                    danger: 'var(--color-rose)',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                editorial: ['"Crimson Pro"', 'Georgia', '"Times New Roman"', 'serif'],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            fontSize: {
                xs: ['var(--text-xs)', { lineHeight: '1.4', letterSpacing: '0' }],
                sm: ['var(--text-sm)', { lineHeight: '1.4', letterSpacing: '0' }],
                base: ['var(--text-base)', { lineHeight: '1.6', letterSpacing: '0' }],
                lg: ['var(--text-lg)', { lineHeight: '1.3', letterSpacing: '0' }],
                xl: ['var(--text-xl)', { lineHeight: '1.2', letterSpacing: '0' }],
                '2xl': ['var(--text-2xl)', { lineHeight: '1.15', letterSpacing: '0' }],
                '3xl': ['var(--text-3xl)', { lineHeight: '1.15', letterSpacing: '0' }],
                '4xl': ['var(--text-4xl)', { lineHeight: '1.1', letterSpacing: '0' }],
            },
            maxWidth: {
                dashboard: '1600px',
            },
            borderRadius: {
                sm: 'var(--radius-sm)',
                md: 'var(--radius-md)',
                lg: 'var(--radius-lg)',
                xl: 'var(--radius-xl)',
            },
            boxShadow: {
                sm: 'var(--shadow-sm)',
                md: 'var(--shadow-md)',
                lg: 'var(--shadow-lg)',
                xl: 'var(--shadow-xl)',
                'glow-emerald': 'var(--shadow-glow-emerald)',
            },
            transitionTimingFunction: {
                default: 'var(--ease-default)',
                decelerate: 'var(--ease-decelerate)',
                accelerate: 'var(--ease-accelerate)',
            },
        },
    },

    plugins: [forms],
};
