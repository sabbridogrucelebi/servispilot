import React from 'react';
import { motion } from 'framer-motion';
import { ArrowRight, ChevronDown } from 'lucide-react';

export default function Hero() {
  return (
    <section 
      style={{ 
        position: 'relative',
        height: 'calc(100vh - 90px)', 
        width: '100%',
        display: 'flex', 
        alignItems: 'center', 
        justifyContent: 'center',
        overflow: 'hidden'
      }}
    >
      {/* Background Image with Parallax-like scale effect */}
      <motion.div 
        initial={{ scale: 1.1 }}
        animate={{ scale: 1 }}
        transition={{ duration: 1.5, ease: "easeOut" }}
        style={{
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%',
          height: '100%',
          backgroundImage: 'url(/images/hero_banner.png)',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          zIndex: 1
        }}
      />

      {/* Dark Gradient Overlay for text readability */}
      <div 
        style={{
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%',
          height: '100%',
          background: 'linear-gradient(to bottom, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0.6) 100%)',
          zIndex: 2
        }}
      />

      {/* Content */}
      <div style={{ position: 'relative', zIndex: 10, textAlign: 'center', maxWidth: '900px', padding: '0 20px' }}>
        <motion.h1 
          initial={{ opacity: 0, y: 30 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.2 }}
          style={{ 
            fontSize: 'clamp(3rem, 6vw, 5rem)', 
            color: '#FFFFFF', 
            lineHeight: 1.1, 
            marginBottom: '24px',
            textShadow: '0 4px 20px rgba(0,0,0,0.5)'
          }}
        >
          Seyahatiniz <br/>
          <span style={{ color: 'var(--color-accent)' }}>Keyif Dolu Olsun</span>
        </motion.h1>
        
        <motion.p 
          initial={{ opacity: 0, y: 30 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.4 }}
          style={{ 
            fontSize: '1.25rem', 
            color: 'rgba(255,255,255,0.9)', 
            marginBottom: '40px', 
            lineHeight: 1.6,
            textShadow: '0 2px 10px rgba(0,0,0,0.5)'
          }}
        >
          Personel, öğrenci ve VIP organizasyonlarınız için güvenli, konforlu ve teknolojinin zirvesinde bir taşıma deneyimi sunuyoruz.
        </motion.p>
        
        <motion.div 
          initial={{ opacity: 0, y: 30 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.6 }}
          style={{ display: 'flex', gap: '16px', justifyContent: 'center', flexWrap: 'wrap' }}
        >
          <motion.a 
            href="#filo" 
            className="btn-primary"
            style={{ padding: '16px 36px', fontSize: '1.125rem' }}
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
          >
            Araç Filomuz
          </motion.a>
          <motion.a 
            href="#teklif" 
            className="btn-outline"
            style={{ padding: '16px 36px', fontSize: '1.125rem', borderColor: 'rgba(255,255,255,0.5)', color: 'white' }}
            whileHover={{ scale: 1.05, backgroundColor: 'rgba(255,255,255,0.1)' }}
            whileTap={{ scale: 0.95 }}
          >
            Hemen Teklif Al
          </motion.a>
        </motion.div>
      </div>

      {/* Scroll Down Arrow */}
      <motion.a 
        href="#hizmetler"
        initial={{ opacity: 0 }}
        animate={{ opacity: 1, y: [0, 10, 0] }}
        transition={{ duration: 2, repeat: Infinity, delay: 1 }}
        style={{ 
          position: 'absolute', 
          bottom: '40px', 
          zIndex: 10,
          color: 'white',
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          gap: '8px',
          textDecoration: 'none'
        }}
      >
        <span style={{ fontSize: '0.875rem', letterSpacing: '2px', textTransform: 'uppercase' }}>Aşağı Kaydır</span>
        <ChevronDown size={32} />
      </motion.a>
    </section>
  );
}
