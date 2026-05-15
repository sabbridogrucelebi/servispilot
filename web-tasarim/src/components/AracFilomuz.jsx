import React from 'react';
import { motion } from 'framer-motion';
import { Bus, Compass, Star, Map } from 'lucide-react';

const vehicles = [
  { id: 'otobus', name: 'OTOBÜSLER', img: '/images/fleet/otobus.png' },
  { id: 'midibus', name: 'MİDİBÜSLER', img: '/images/fleet/midibus.png' },
  { id: 'minibus', name: 'MİNİBÜSLER', img: '/images/fleet/minibus.png' },
  { id: 'vip', name: 'VIP ARAÇLAR', img: '/images/fleet/vip.png' },
  { id: 'otomobil', name: 'OTOMOBİLLER', img: '/images/fleet/otomobil.png' }
];

export default function AracFilomuz({ onVehicleClick }) {
  return (
    <div id="filomuz" style={{ padding: '0 5% 100px', marginTop: '-40px', background: '#ffffff', position: 'relative', overflow: 'hidden' }}>
      
      {/* 3D Background Icons (Premium) */}
      <motion.div
        animate={{ y: [0, -30, 0], rotate: [0, 5, 0] }}
        transition={{ duration: 15, repeat: Infinity, ease: "easeInOut" }}
        style={{ position: 'absolute', top: '5%', left: '5%', opacity: 0.03, color: '#10549c', pointerEvents: 'none', zIndex: 0 }}
      >
        <Bus size={500} />
      </motion.div>
      <motion.div
        animate={{ y: [0, 40, 0], rotate: [0, -5, 0] }}
        transition={{ duration: 20, repeat: Infinity, ease: "easeInOut" }}
        style={{ position: 'absolute', bottom: '-10%', right: '5%', opacity: 0.03, color: '#10549c', pointerEvents: 'none', zIndex: 0 }}
      >
        <Compass size={600} />
      </motion.div>
      <motion.div
        animate={{ scale: [1, 1.1, 1], opacity: [0.02, 0.04, 0.02] }}
        transition={{ duration: 10, repeat: Infinity, ease: "easeInOut" }}
        style={{ position: 'absolute', top: '40%', left: '40%', opacity: 0.03, color: '#e21b1b', pointerEvents: 'none', zIndex: 0 }}
      >
        <Star size={400} />
      </motion.div>

      {/* Vehicles Grid */}
      <div style={{ 
        maxWidth: '1800px', 
        margin: '0 auto', 
        display: 'grid', 
        gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', 
        gap: '80px',
        alignItems: 'end',
        position: 'relative',
        zIndex: 10
      }}>
        {vehicles.map((v, i) => (
          <motion.div 
            key={v.id}
            initial={{ opacity: 0, y: 40 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: i * 0.15, duration: 0.7, type: 'spring' }}
            whileHover="hover"
            onClick={() => onVehicleClick && onVehicleClick(v.id)}
            style={{ 
              display: 'flex', 
              flexDirection: 'column', 
              alignItems: 'center', 
              cursor: 'pointer',
              position: 'relative',
              padding: '20px',
              borderRadius: '24px',
              transition: 'background 0.3s ease'
            }}
          >
            {/* Hover State Background */}
            <motion.div
              style={{
                position: 'absolute',
                top: 0, left: 0, right: 0, bottom: 0,
                background: 'radial-gradient(ellipse at bottom, rgba(16,84,156,0.05) 0%, transparent 70%)',
                borderRadius: '24px',
                opacity: 0,
                zIndex: -1
              }}
              variants={{ hover: { opacity: 1, y: -15 } }}
            />

            {/* Image Container */}
            <div style={{ 
              width: '100%', 
              height: '350px', 
              display: 'flex', 
              alignItems: 'flex-end', 
              justifyContent: 'center',
              marginBottom: '30px',
              position: 'relative'
            }}>
              {/* Premium Shadow */}
              <div style={{
                position: 'absolute',
                bottom: '-20px',
                width: '85%',
                height: '25px',
                background: 'radial-gradient(ellipse, rgba(0,0,0,0.2) 0%, transparent 70%)',
                filter: 'blur(8px)',
                zIndex: 0
              }}></div>
              
              <motion.img 
                src={v.img} 
                alt={v.name} 
                variants={{
                  hover: { scale: 1.1, x: 25, y: -20, rotate: -3, filter: 'drop-shadow(0 30px 40px rgba(0,0,0,0.3))' }
                }}
                transition={{ type: 'spring', stiffness: 300, damping: 20 }}
                style={{ 
                  width: '130%',
                  maxWidth: 'none', 
                  maxHeight: 'none', 
                  objectFit: 'contain',
                  position: 'relative',
                  zIndex: 1,
                  filter: 'drop-shadow(0 20px 25px rgba(0,0,0,0.15))'
                }} 
                onError={(e) => {
                  e.target.onerror = null;
                  e.target.src = 'https://placehold.co/600x300/f8fafc/10549c?text=' + encodeURIComponent(v.name) + '+Görseli';
                }}
              />
            </div>

            {/* Title */}
            <div style={{
              width: '100%',
              borderTop: '2px solid rgba(16,84,156,0.1)',
              paddingTop: '25px',
              textAlign: 'center'
            }}>
              <motion.h3 
                variants={{ hover: { y: -5, color: '#e21b1b' } }}
                transition={{ duration: 0.3 }}
                style={{
                fontSize: '1.4rem',
                fontWeight: 900,
                color: 'var(--color-heading)',
                letterSpacing: '2px',
                fontFamily: 'var(--font-heading)'
              }}>
                {v.name}
              </motion.h3>
            </div>
          </motion.div>
        ))}
      </div>
      
      {/* Bottom separator */}
      <div style={{
        position: 'absolute',
        bottom: 0, left: 0, right: 0, height: '1px',
        background: 'linear-gradient(90deg, transparent 0%, rgba(16,84,156,0.1) 50%, transparent 100%)',
        pointerEvents: 'none'
      }}></div>
    </div>
  );
}
