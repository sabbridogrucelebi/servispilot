import React from 'react';
import { motion } from 'framer-motion';

export default function WhatsAppButton() {
  const phoneNumber = "905324733564";
  const message = encodeURIComponent("Merhaba, Mehmet Çelebi Turizm web siteniz üzerinden ulaşıyorum. Kurumsal hizmetleriniz hakkında detaylı bilgi almak istiyorum.");
  const whatsappUrl = `https://wa.me/${phoneNumber}?text=${message}`;

  return (
    <motion.a
      href={whatsappUrl}
      target="_blank"
      rel="noopener noreferrer"
      initial={{ scale: 0, opacity: 0 }}
      animate={{ scale: 1, opacity: 1 }}
      transition={{ type: 'spring', stiffness: 260, damping: 20, delay: 1 }}
      whileHover={{ scale: 1.1, rotate: -5 }}
      whileTap={{ scale: 0.9 }}
      style={{
        position: 'fixed',
        bottom: '40px',
        right: '40px',
        zIndex: 9999,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        width: '70px',
        height: '70px',
        background: 'linear-gradient(135deg, #25D366 0%, #128C7E 100%)',
        borderRadius: '50%',
        boxShadow: '0 15px 35px rgba(37, 211, 102, 0.4), inset 0 -4px 10px rgba(0,0,0,0.2), inset 0 4px 10px rgba(255,255,255,0.4)',
        cursor: 'pointer',
        textDecoration: 'none'
      }}
    >
      {/* 3D Pulse Effect Backgrounds */}
      <motion.div
        animate={{ scale: [1, 1.4, 1], opacity: [0.5, 0, 0.5] }}
        transition={{ duration: 2.5, repeat: Infinity, ease: "easeInOut" }}
        style={{
          position: 'absolute',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          background: '#25D366',
          borderRadius: '50%',
          zIndex: -1
        }}
      />
      <motion.div
        animate={{ scale: [1, 1.7, 1], opacity: [0.3, 0, 0.3] }}
        transition={{ duration: 2.5, repeat: Infinity, ease: "easeInOut", delay: 0.5 }}
        style={{
          position: 'absolute',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          background: '#128C7E',
          borderRadius: '50%',
          zIndex: -2
        }}
      />

      {/* SVG Icon */}
      <svg 
        xmlns="http://www.w3.org/2000/svg" 
        viewBox="0 0 24 24" 
        width="40" 
        height="40" 
        fill="white"
        style={{ filter: 'drop-shadow(0 4px 6px rgba(0,0,0,0.3))' }}
      >
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
      </svg>
      
      {/* Premium Notification Dot */}
      <div style={{
        position: 'absolute',
        top: '2px',
        right: '2px',
        width: '18px',
        height: '18px',
        background: '#e21b1b',
        borderRadius: '50%',
        border: '3px solid white',
        boxShadow: '0 2px 5px rgba(0,0,0,0.2)'
      }} />
    </motion.a>
  );
}
