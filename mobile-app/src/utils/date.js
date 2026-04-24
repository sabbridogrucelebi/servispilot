/**
 * Converts DD.MM.YYYY to YYYY-MM-DD for API compatibility
 */
export const toApiDate = (dateStr) => {
    if (!dateStr || !dateStr.includes('.')) return dateStr;
    const [day, month, year] = dateStr.split('.');
    return `${year}-${month}-${day}`;
};

/**
 * Converts YYYY-MM-DD to DD.MM.YYYY for UI display
 */
export const toUiDate = (dateStr) => {
    if (!dateStr || !dateStr.includes('-')) return dateStr;
    const [year, month, day] = dateStr.split('T')[0].split('-');
    return `${day}.${month}.${year}`;
};

export const todayUi = () => {
    const d = new Date();
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}.${month}.${year}`;
};
