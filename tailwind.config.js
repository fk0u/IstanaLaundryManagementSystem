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
                sans: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['Outfit', '"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Material Design 3 Expressive Color System
                primary: {
                    DEFAULT: '#FF6600', // Expressive Vibrant Primary
                    hover: '#E55C00',
                    focus: '#CC5200',
                    container: '#FFDBC9',
                    'on-container': '#341000',
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
                surface: {
                    DEFAULT: '#FFF8F6',
                    dim: '#E8D6D1',
                    bright: '#FFF8F6',
                    container: '#FBEAE5',
                    'container-high': '#F5E4DF',
                    'container-highest': '#EFE0DB',
                    'on-surface': '#231A17',
                    'on-variant': '#53433F',
                    outline: '#85736E',
                    'outline-variant': '#D8C2BC',
                },
                md3: {
                    primary: '#6750A4',
                    'on-primary': '#FFFFFF',
                    'primary-container': '#EADDFF',
                    'on-primary-container': '#21005D',
                    surface: '#FEF7FF',
                    'surface-container-low': '#F7F2FA',
                    'surface-container': '#F3EDF7',
                    'surface-container-high': '#ECE6F0',
                    'surface-container-highest': '#E6E0E9',
                    'on-surface': '#1D1B20',
                    'on-surface-variant': '#49454F',
                    outline: '#79747E',
                    'outline-variant': '#CAC4D0',
                }
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
                '4xl': '2rem',
                'expressive': '1.75rem',
            },
            boxShadow: {
                'md3-1': '0px 1px 3px 1px rgba(0, 0, 0, 0.15), 0px 1px 2px 0px rgba(0, 0, 0, 0.30)',
                'md3-2': '0px 2px 6px 2px rgba(0, 0, 0, 0.15), 0px 1px 2px 0px rgba(0, 0, 0, 0.30)',
                'md3-3': '0px 4px 8px 3px rgba(0, 0, 0, 0.15), 0px 1px 3px 0px rgba(0, 0, 0, 0.30)',
                'md3-4': '0px 6px 10px 4px rgba(0, 0, 0, 0.15), 0px 2px 4px 0px rgba(0, 0, 0, 0.30)',
            },
            fontSize: {
                '2xs': ['0.625rem', { lineHeight: '0.875rem' }],
            },
        },
    },

    plugins: [forms],
};
