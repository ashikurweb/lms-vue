/**
 * Truncate text to a specified length
 * @param {string} text 
 * @param {number} length 
 * @param {string} suffix 
 * @returns {string}
 */
export function truncateText(text, length = 60, suffix = '...') {
    if (!text) return '';
    if (text.length <= length) return text;
    return text.substring(0, length).trim() + suffix;
}

/**
 * Format number with k/m suffix
 * @param {number} num 
 * @returns {string}
 */
export function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'k';
    return num;
}

/**
 * Format date string to a human readable format
 * @param {string} dateString 
 * @returns {string}
 */
export function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    }).format(date);
}
