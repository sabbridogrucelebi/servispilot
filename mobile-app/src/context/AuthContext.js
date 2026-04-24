import React, { createContext, useState, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import api from '../api/axios';

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
    const [isLoading, setIsLoading] = useState(false);
    const [userToken, setUserToken] = useState(null);
    const [userInfo, setUserInfo] = useState(null);

    const login = async (email, password) => {
        setIsLoading(true);
        try {
            const response = await api.post('/login', {
                email,
                password,
                device_name: 'mobile-app'
            });

            if (response.data.token) {
                setUserInfo(response.data.user);
                setUserToken(response.data.token);
                await AsyncStorage.setItem('userInfo', JSON.stringify(response.data.user));
                await AsyncStorage.setItem('userToken', response.data.token);
            }
        } catch (error) {
            console.error('Login error', error.response?.data || error.message);
            throw error;
        } finally {
            setIsLoading(false);
        }
    };

    const logout = async () => {
        setIsLoading(true);
        try {
            await api.post('/logout');
        } catch (e) {
            console.error(e);
        }
        setUserToken(null);
        setUserInfo(null);
        await AsyncStorage.removeItem('userInfo');
        await AsyncStorage.removeItem('userToken');
        setIsLoading(false);
    };

    const isLoggedIn = async () => {
        try {
            setIsLoading(true);
            let userInfo = await AsyncStorage.getItem('userInfo');
            let userToken = await AsyncStorage.getItem('userToken');
            
            if (userInfo && userToken) {
                setUserInfo(JSON.parse(userInfo));
                setUserToken(userToken);
                
                // Token doğrulaması yap
                try {
                    const res = await api.get('/me');
                    setUserInfo(res.data.user);
                    await AsyncStorage.setItem('userInfo', JSON.stringify(res.data.user));
                } catch (e) {
                    // Token geçersizse çıkış yap
                    logout();
                }
            }
        } catch (e) {
            console.error(e);
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        isLoggedIn();
    }, []);

    return (
        <AuthContext.Provider value={{ login, logout, isLoading, userToken, userInfo }}>
            {children}
        </AuthContext.Provider>
    );
};
