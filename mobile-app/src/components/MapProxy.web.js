import React from 'react';
import { View, Text } from 'react-native';

export const MapView = ({ style, children }) => (
    <View style={[style, { alignItems: 'center', justifyContent: 'center', backgroundColor: '#E2E8F0' }]}>
        <Text style={{ color: '#475569', fontSize: 16, fontWeight: 'bold' }}>Harita Web'de Devre Dışı</Text>
        <Text style={{ color: '#475569', marginTop: 8 }}>Lütfen mobil cihazdan (Expo Go) test ediniz.</Text>
        {children}
    </View>
);

export const Marker = () => null;
