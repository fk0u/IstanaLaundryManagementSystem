import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    safelist: [
        'material-symbols-outlined',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['Outfit', '"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Neumorphism Surface Palette
                'nm': {
                    bg: 'var(--nm-bg)',
                    'bg-secondary': 'var(--nm-bg-secondary)',
                    surface: 'var(--nm-surface)',
                    'surface-high': 'var(--nm-surface-high)',
                },
                // Brand Colors (preserved)
                primary: {
                    DEFAULT: '#FF6600',
                    hover: '#E55C00',
                    focus: '#CC5200',
                    container: '#FFDBC9',
                    'on-container': '#341000',
                    soft: 'rgba(255, 102, 0, 0.12)',
                    glow: 'rgba(255, 102, 0, 0.25)',
                },
                secondary: {
                    DEFAULT: '#77574E',
                    container: '#FFDBCF',
                    'on-container': '#2C160F',
                },
                tertiary: {
                    DEFAULT: '#6B5D2F',
                    container: '#F5E1A7',
                    'on-container': '#231B00',
                },
                // Neumorphism-aware surface tokens
                surface: {
                    DEFAULT: 'var(--nm-bg)',
                    dim: 'var(--nm-bg)',
                    bright: 'var(--nm-surface-high)',
                    container: 'var(--nm-surface)',
                    'container-high': 'var(--nm-surface-high)',
                    'container-highest': 'var(--nm-surface-high)',
                    'container-low': 'var(--nm-bg-secondary)',
                    'on-surface': 'var(--text-primary)',
                    'on-variant': 'var(--text-secondary)',
                    outline: 'var(--text-tertiary)',
                    'outline-variant': 'rgba(0,0,0,0.06)',
                },
                md3: {
                    primary: '#6750A4',
                    'on-primary': '#FFFFFF',
                    'primary-container': '#EADDFF',
                    'on-primary-container': '#21005D',
                    surface: 'var(--nm-bg)',
                    'surface-container-low': 'var(--nm-bg-secondary)',
                    'surface-container': 'var(--nm-surface)',
                    'surface-container-high': 'var(--nm-surface-high)',
                    'surface-container-highest': 'var(--nm-surface-high)',
                    'on-surface': 'var(--text-primary)',
                    'on-surface-variant': 'var(--text-secondary)',
                    outline: 'var(--text-tertiary)',
                    'outline-variant': 'rgba(0,0,0,0.06)',
                },
            },
            spacing: {
                'safe-bottom': '5.5rem',
            },
            screens: {
                'xs': '375px',
            },
            borderRadius: {
                'sm': '8px',
                'md': '12px',
                'lg': '16px',
                'xl': '20px',
                '2xl': '24px',
                '3xl': '28px',
                '4xl': '32px',
                'expressive': '20px',
            },
            boxShadow: {
                // Neumorphism elevation system
                'nm-raised': 'var(--nm-raised)',
                'nm-raised-sm': 'var(--nm-raised-sm)',
                'nm-raised-lg': 'var(--nm-raised-lg)',
                'nm-inset': 'var(--nm-inset)',
                'nm-inset-sm': 'var(--nm-inset-sm)',
                'nm-convex': 'var(--nm-convex)',
                'nm-pressed': 'var(--nm-pressed)',
                // Legacy MD3 shadows (mapped to neumorphism)
                'md3-1': 'var(--nm-raised-sm)',
                'md3-2': 'var(--nm-raised)',
                'md3-3': 'var(--nm-raised-lg)',
                'md3-4': 'var(--nm-raised-lg)',
            },
            fontSize: {
                '2xs': ['0.625rem', { lineHeight: '0.875rem' }],
            },
        },
    },

    plugins: [forms],
};
