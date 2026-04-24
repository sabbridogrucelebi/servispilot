import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Platform, TextInput } from 'react-native';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import DateTimePicker from '@react-native-community/datetimepicker';

export default function DatePickerInput({ label, value, onChange, placeholder = 'gg.aa.yyyy' }) {
    const [show, setShow] = useState(false);
    const [date, setDate] = useState(new Date());

    const onDateChange = (event, selectedDate) => {
        setShow(false);
        if (selectedDate) {
            setDate(selectedDate);
            const day = String(selectedDate.getDate()).padStart(2, '0');
            const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
            const year = selectedDate.getFullYear();
            const formatted = `${day}.${month}.${year}`;
            onChange(formatted);
        }
    };

    if (Platform.OS === 'web') {
        const toWebDate = (val) => {
            if (!val || !val.includes('.')) return '';
            const [d, m, y] = val.split('.');
            return `${y}-${m}-${d}`;
        };
        const fromWebDate = (val) => {
            if (!val) return '';
            const [y, m, d] = val.split('-');
            return `${d}.${m}.${y}`;
        };

        return (
            <View style={s.container}>
                {label ? <Text style={s.label}>{label}</Text> : null}
                <div style={{ position: 'relative', width: '100%' }}>
                    <input 
                        type="date" 
                        value={toWebDate(value)}
                        onChange={(e) => onChange(fromWebDate(e.target.value))}
                        style={{
                            width: '100%',
                            padding: '10px 12px',
                            borderRadius: '12px',
                            border: '2px solid #F1F5F9',
                            fontSize: '13px',
                            fontWeight: '600',
                            fontFamily: 'inherit',
                            color: '#1E293B',
                            backgroundColor: '#F8FAFC',
                            appearance: 'none',
                            outline: 'none',
                            boxSizing: 'border-box',
                            transition: 'all 0.3s ease'
                        }}
                        onFocus={(e) => {
                            e.target.style.borderColor = '#4F46E5';
                            e.target.style.backgroundColor = '#fff';
                        }}
                        onBlur={(e) => {
                            e.target.style.borderColor = '#F1F5F9';
                            e.target.style.backgroundColor = '#F8FAFC';
                        }}
                    />
                </div>
            </View>
        );
    }

    return (
        <View style={s.container}>
            {label ? <Text style={s.label}>{label}</Text> : null}
            <TouchableOpacity style={s.input} onPress={() => setShow(true)} activeOpacity={0.7}>
                <Text style={[s.value, !value && s.placeholder]}>{value || placeholder}</Text>
                <Icon name="calendar-month" size={18} color="#4F46E5" />
            </TouchableOpacity>

            {show && (
                <DateTimePicker
                    testID="dateTimePicker"
                    value={date}
                    mode="date"
                    is24Hour={true}
                    display="default"
                    onChange={onDateChange}
                />
            )}
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, marginHorizontal: 4 },
    label: { fontSize: 10, fontWeight: '800', color: '#64748B', marginBottom: 4, textTransform: 'uppercase', letterSpacing: 0.5, marginLeft: 2 },
    input: { 
        flexDirection: 'row', 
        alignItems: 'center', 
        justifyContent: 'space-between',
        backgroundColor: '#F8FAFC', 
        borderWidth: 2, 
        borderColor: '#F1F5F9', 
        borderRadius: 12, 
        paddingHorizontal: 12,
        paddingVertical: 10,
    },
    value: { fontSize: 13, fontWeight: '600', color: '#1E293B' },
    placeholder: { color: '#94A3B8', fontWeight: '400' }
});
