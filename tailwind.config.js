import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#FF6600',
                'primary-hover': '#E65C00',
                'primary-container': '#FFF3E0',
                surface: '#FAFBFC',
                'on-surface': '#1E293B',
                'on-surface-variant': '#64748B',
                'outline-variant': '#E2E8F0',
            },
            spacing: {
                'safe-bottom': '5.5rem',
            },
            screens: {
                'xs': '375px',
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.5rem',
            },
            fontSize: {
                '2xs': ['0.625rem', { lineHeight: '0.875rem' }],
            },
        },
    },

    plugins: [forms],
};
