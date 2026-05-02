import React from 'react';
import { View } from 'react-native';
import Svg, { Path, Circle, Rect, Defs, LinearGradient, Stop } from 'react-native-svg';

// Parıldayan 3D Premium SVG İkon Seti
export const PremiumIcons = {
    HomeTab: ({ color, size = 28 }) => (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
            <Defs>
                <LinearGradient id="grad1" x1="0" y1="0" x2="1" y2="1">
                    <Stop offset="0%" stopColor={color} stopOpacity="1" />
                    <Stop offset="100%" stopColor={color} stopOpacity="0.3" />
                </LinearGradient>
            </Defs>
            <Rect x="3" y="3" width="7" height="7" rx="2" fill="url(#grad1)" />
            <Rect x="14" y="3" width="7" height="7" rx="2" fill="url(#grad1)" opacity="0.7" />
            <Rect x="3" y="14" width="7" height="7" rx="2" fill="url(#grad1)" opacity="0.5" />
            <Rect x="14" y="14" width="7" height="7" rx="2" fill="url(#grad1)" opacity="0.9" />
        </Svg>
    ),
    Vehicles: ({ color, size = 28 }) => (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
            <Defs>
                <LinearGradient id="grad2" x1="0" y1="0" x2="1" y2="1">
                    <Stop offset="0%" stopColor={color} stopOpacity="1" />
                    <Stop offset="100%" stopColor={color} stopOpacity="0.2" />
                </LinearGradient>
            </Defs>
            <Path d="M3 13h18l-2-7H5l-2 7z" fill="url(#grad2)" opacity="0.5" />
            <Path d="M2 13h20v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" fill="url(#grad2)" />
            <Circle cx="6" cy="15" r="1.5" fill="#fff" opacity="0.8" />
            <Circle cx="18" cy="15" r="1.5" fill="#fff" opacity="0.8" />
        </Svg>
    ),
    Personnel: ({ color, size = 28 }) => (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
            <Defs>
                <LinearGradient id="grad3" x1="0" y1="0" x2="1" y2="1">
                    <Stop offset="0%" stopColor={color} stopOpacity="1" />
                    <Stop offset="100%" stopColor={color} stopOpacity="0.3" />
                </LinearGradient>
            </Defs>
            <Circle cx="12" cy="7" r="4" fill="url(#grad3)" />
            <Circle cx="6" cy="10" r="3" fill="url(#grad3)" opacity="0.5" />
            <Circle cx="18" cy="10" r="3" fill="url(#grad3)" opacity="0.5" />
            <Path d="M12 13c-4 0-8 3-8 7h16c0-4-4-7-8-7z" fill="url(#grad3)" opacity="0.9" />
        </Svg>
    ),
    Trips: ({ color, size = 28 }) => (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
            <Defs>
                <LinearGradient id="grad4" x1="0" y1="0" x2="1" y2="1">
                    <Stop offset="0%" stopColor={color} stopOpacity="1" />
                    <Stop offset="100%" stopColor={color} stopOpacity="0.2" />
                </LinearGradient>
            </Defs>
            <Path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" fill="url(#grad4)" opacity="0.6" />
            <Circle cx="12" cy="9" r="2.5" fill="#fff" opacity="0.9" />
            <Path d="M5 9c0 3.87 3.13 7 7 7s7-3.13 7-7" stroke="url(#grad4)" strokeWidth="1.5" strokeDasharray="2 2" />
        </Svg>
    ),
    Payrolls: ({ color, size = 28 }) => (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
            <Defs>
                <LinearGradient id="grad5" x1="0" y1="0" x2="1" y2="1">
                    <Stop offset="0%" stopColor={color} stopOpacity="1" />
                    <Stop offset="100%" stopColor={color} stopOpacity="0.3" />
                </LinearGradient>
            </Defs>
            <Rect x="3" y="6" width="18" height="12" rx="2" fill="url(#grad5)" opacity="0.5" />
            <Path d="M3 10h18v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6z" fill="url(#grad5)" />
            <Circle cx="12" cy="12" r="2" fill="#fff" opacity="0.8" />
        </Svg>
    ),
    Customers: ({ color, size = 28 }) => (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
            <Defs>
                <LinearGradient id="grad6" x1="0" y1="0" x2="1" y2="1">
                    <Stop offset="0%" stopColor={color} stopOpacity="1" />
                    <Stop offset="100%" stopColor={color} stopOpacity="0.3" />
                </LinearGradient>
            </Defs>
            <Path d="M4 21V7l8-4 8 4v14H4z" fill="url(#grad6)" opacity="0.7" />
            <Rect x="10" y="15" width="4" height="6" fill="#fff" opacity="0.8" />
            <Rect x="7" y="9" width="2" height="2" fill="#fff" opacity="0.5" />
            <Rect x="11" y="9" width="2" height="2" fill="#fff" opacity="0.5" />
            <Rect x="15" y="9" width="2" height="2" fill="#fff" opacity="0.5" />
        </Svg>
    ),
    Reports: ({ color, size = 28 }) => (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
            <Defs>
                <LinearGradient id="grad7" x1="0" y1="0" x2="1" y2="1">
                    <Stop offset="0%" stopColor={color} stopOpacity="1" />
                    <Stop offset="100%" stopColor={color} stopOpacity="0.3" />
                </LinearGradient>
            </Defs>
            <Path d="M11 2v10h10C21 6.48 16.52 2 11 2z" fill="url(#grad7)" opacity="1" />
            <Path d="M9 3.12C4.42 4.47 1 8.84 1 14c0 6.08 4.92 11 11 11 4.7 0 8.73-2.95 10.36-7H9V3.12z" fill="url(#grad7)" opacity="0.5" />
        </Svg>
    ),
    Activity: ({ color, size = 28 }) => (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
            <Defs>
                <LinearGradient id="grad8" x1="0" y1="0" x2="1" y2="1">
                    <Stop offset="0%" stopColor={color} stopOpacity="1" />
                    <Stop offset="100%" stopColor={color} stopOpacity="0.3" />
                </LinearGradient>
            </Defs>
            <Path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2z" fill="url(#grad8)" />
            <Path d="M18 16v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z" fill="url(#grad8)" opacity="0.7" />
        </Svg>
    ),
    Settings: ({ color, size = 28 }) => (
        <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
            <Defs>
                <LinearGradient id="grad9" x1="0" y1="0" x2="1" y2="1">
                    <Stop offset="0%" stopColor={color} stopOpacity="1" />
                    <Stop offset="100%" stopColor={color} stopOpacity="0.3" />
                </LinearGradient>
            </Defs>
            <Path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.06-.94l2.03-1.58a.49.49 0 00.12-.61l-1.92-3.32a.488.488 0 00-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54a.484.484 0 00-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.73 8.87a.49.49 0 00.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.06.94l-2.03 1.58a.49.49 0 00-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .43-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.49-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z" fill="url(#grad9)" />
            <Circle cx="12" cy="12" r="2" fill="#fff" opacity="0.9" />
        </Svg>
    )
};

// Wrapper bileşen (Glow efekti için)
export const GlowIcon = ({ name, color, size }) => {
    const IconComponent = PremiumIcons[name];
    if (!IconComponent) return null;
    
    return (
        <View style={{
            shadowColor: color,
            shadowOffset: { width: 0, height: 0 },
            shadowOpacity: 0.8,
            shadowRadius: 15,
            elevation: 10
        }}>
            <IconComponent color={color} size={size} />
        </View>
    );
};
