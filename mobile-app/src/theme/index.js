// ─── ServisPilot Tema Sistemi ───
// Türkçe (Latin Extended) karakter seti destekli Inter font ailesi

// Font ailesi tanımları — Inter, Türkçe karakterleri (ı, İ, ş, Ş, ğ, Ğ, ç, Ç, ö, Ö, ü, Ü) tam destekler
export const fontFamily = {
    regular: 'Inter_400Regular',
    medium: 'Inter_500Medium',
    semiBold: 'Inter_600SemiBold',
    bold: 'Inter_700Bold',
    extraBold: 'Inter_800ExtraBold',
    black: 'Inter_900Black',
};

export const colors = {
    // Module colors
    customers:   { 50: '#EFF6FF', 100: '#DBEAFE', 500: '#3B82F6', 600: '#2563EB', 700: '#1D4ED8' },
    trips:       { 50: '#F0FDFA', 100: '#CCFBF1', 500: '#14B8A6', 600: '#0D9488', 700: '#0F766E' },
    fuel:        { 50: '#FFFBEB', 100: '#FEF3C7', 500: '#F59E0B', 600: '#D97706', 700: '#B45309' },
    fuels:       { 50: '#FFFBEB', 100: '#FEF3C7', 500: '#F59E0B', 600: '#D97706', 700: '#B45309' },
    maintenance: { 50: '#FAF5FF', 100: '#F3E8FF', 500: '#A855F7', 600: '#9333EA', 700: '#7E22CE' },
    maintenances:{ 50: '#FAF5FF', 100: '#F3E8FF', 500: '#A855F7', 600: '#9333EA', 700: '#7E22CE' },
    personnel:   { 50: '#FDF2F8', 100: '#FCE7F3', 500: '#EC4899', 600: '#DB2777', 700: '#BE185D' },
    penalty:     { 50: '#FEF2F2', 100: '#FEE2E2', 500: '#EF4444', 600: '#DC2626', 700: '#B91C1C' },
    penalties:   { 50: '#FEF2F2', 100: '#FEE2E2', 500: '#EF4444', 600: '#DC2626', 700: '#B91C1C' },
    document:    { 50: '#F0F9FF', 100: '#E0F2FE', 500: '#0EA5E9', 600: '#0284C7', 700: '#0369A1' },
    documents:   { 50: '#F0F9FF', 100: '#E0F2FE', 500: '#0EA5E9', 600: '#0284C7', 700: '#0369A1' },
    payroll:     { 50: '#ECFDF5', 100: '#D1FAE5', 500: '#10B981', 600: '#059669', 700: '#047857' },
    payrolls:    { 50: '#ECFDF5', 100: '#D1FAE5', 500: '#10B981', 600: '#059669', 700: '#047857' },
    contract:    { 50: '#FFF7ED', 100: '#FFEDD5', 500: '#F97316', 600: '#EA580C', 700: '#C2410C' },
    contracts:   { 50: '#FFF7ED', 100: '#FFEDD5', 500: '#F97316', 600: '#EA580C', 700: '#C2410C' },
    route:       { 50: '#EEF2FF', 100: '#E0E7FF', 500: '#6366F1', 600: '#4F46E5', 700: '#4338CA' },
    routes:      { 50: '#EEF2FF', 100: '#E0E7FF', 500: '#6366F1', 600: '#4F46E5', 700: '#4338CA' },
    vehicles:    { 50: '#F4F4F5', 100: '#E4E4E7', 500: '#71717A', 600: '#52525B', 700: '#3F3F46' },

    // Neutrals
    bg:          '#F8FAFC',
    surface:     '#FFFFFF',
    surfaceAlt:  '#F1F5F9',
    border:      '#E2E8F0',
    textPrimary: '#0F172A',
    textSecondary: '#475569',
    textMuted:   '#94A3B8',

    // Semantic (Objects to prevent undefined errors, also kept string versions for backwards compatibility just in case)
    primary:     { 50: '#EFF6FF', 100: '#DBEAFE', 400: '#60A5FA', 500: '#3B82F6', 600: '#2563EB', 700: '#1D4ED8' },
    success:     { 50: '#ECFDF5', 100: '#D1FAE5', 500: '#10B981', 600: '#059669', 700: '#047857', toString: () => '#10B981' },
    warning:     { 50: '#FFFBEB', 100: '#FEF3C7', 500: '#F59E0B', 600: '#D97706', 700: '#B45309', toString: () => '#F59E0B' },
    danger:      { 50: '#FEF2F2', 100: '#FEE2E2', 500: '#EF4444', 600: '#DC2626', 700: '#B91C1C', toString: () => '#EF4444' },
    info:        { 50: '#EFF6FF', 100: '#DBEAFE', 500: '#3B82F6', 600: '#2563EB', 700: '#1D4ED8', toString: () => '#3B82F6' },
};

export const spacing = { xs: 4, sm: 8, md: 12, lg: 16, xl: 24, xxl: 32, xxxl: 48 };

export const radius = { sm: 6, md: 10, lg: 14, xl: 20, full: 999 };

export const typography = {
    display:   { fontSize: 28, fontFamily: fontFamily.black, letterSpacing: -0.5 },
    title:     { fontSize: 20, fontFamily: fontFamily.bold },
    heading:   { fontSize: 17, fontFamily: fontFamily.semiBold },
    body:      { fontSize: 15, fontFamily: fontFamily.regular },
    caption:   { fontSize: 13, fontFamily: fontFamily.regular },
    label:     { fontSize: 12, fontFamily: fontFamily.semiBold, letterSpacing: 0.4, textTransform: 'uppercase' },
};

export const shadow = {
    sm: { shadowColor: '#0F172A', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.06, shadowRadius: 2, elevation: 1 },
    md: { shadowColor: '#0F172A', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.08, shadowRadius: 8, elevation: 3 },
    lg: { shadowColor: '#0F172A', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.12, shadowRadius: 16, elevation: 6 },
};