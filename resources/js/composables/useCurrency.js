import { ref, onMounted } from 'vue';
import api from '../services/api';

export function useCurrency() {
    const defaultCurrency = ref({
        code: 'USD',
        symbol: '$',
        decimal_digits: 2,
        decimal_separator: '.',
        thousands_separator: ',',
        pattern: '{symbol}{amount}'
    });

    const currentCurrency = ref(null);
    const loading = ref(false);

    const fetchDefaultCurrency = async () => {
        loading.value = true;
        try {
            const response = await api.get('/admin/currencies/default');
            if (response.data && response.data.data) {
                currentCurrency.value = response.data.data;
            }
        } catch (error) {
            console.error('Failed to fetch default currency:', error);
            currentCurrency.value = defaultCurrency.value;
        } finally {
            loading.value = false;
        }
    };

    const formatPrice = (amount) => {
        const currency = currentCurrency.value || defaultCurrency.value;
        
        const decimalDigits = currency.decimal_digits ?? 2;
        const decimalSeparator = currency.decimal_separator ?? '.';
        const thousandsSeparator = currency.thousands_separator ?? ',';
        const pattern = currency.pattern ?? '{symbol}{amount}';

        // Custom number formatting to match PHP's number_format
        let parts = parseFloat(amount).toFixed(decimalDigits).split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSeparator);
        const formattedAmount = parts.join(decimalSeparator);

        return pattern.replace('{symbol}', currency.symbol)
                      .replace('{amount}', formattedAmount);
    };

    onMounted(() => {
        fetchDefaultCurrency();
    });

    return {
        currentCurrency,
        loading,
        formatPrice,
        fetchDefaultCurrency
    };
}
