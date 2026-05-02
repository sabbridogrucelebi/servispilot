import React from 'react';
import { View, Text, StyleSheet, SafeAreaView } from 'react-native';
import SpaceWaves from '../components/SpaceWaves';

export default function ParentNotificationsScreen() {
    return (
        <SafeAreaView style={styles.container}>
            <SpaceWaves />
            <View style={styles.content}>
                <Text style={styles.title}>Bildirimler</Text>
                <Text style={styles.text}>Öğrenci bindi/indi ve sistem bildirimleri burada yer alacaktır.</Text>
            </View>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#020617' },
    content: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 20 },
    title: { color: '#fff', fontSize: 24, fontWeight: 'bold', marginBottom: 10 },
    text: { color: '#94A3B8', fontSize: 14, textAlign: 'center' }
});
