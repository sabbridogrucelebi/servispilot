import React from 'react';
import { motion } from 'framer-motion';
import { Users, GraduationCap, Compass, Star, Key } from 'lucide-react';

const services = [
  { title: 'PERSONEL TAŞIMACILIĞI', icon: Users },
  { title: 'ÖĞRENCİ TAŞIMACILIĞI', icon: GraduationCap },
  { title: 'TURİZM TAŞIMACILIĞI', icon: Compass },
  { title: 'VIP TRANSFER', icon: Star },
  { title: 'ARAÇ KİRALAMA', icon: Key },
];

export default function QuickServices({ activeIndex = 0, onServiceClick }) {
  return (
    <div style={{ position: 'relative', zIndex: 20, width: '100%', padding: '0 5%', marginTop: '-55px' }}>
      <motion.div 
        initial={{ opacity: 0, y: 50 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8, delay: 0.2 }}
        style={{ 
          maxWidth: '1200px', 
          margin: '0 auto', 
          display: 'flex',
          borderRadius: '100px',
          boxShadow: '0 25px 50px rgba(0,0,0,0.25), 0 10px 20px rgba(16, 84, 156, 0.1)',
          overflow: 'visible',
          position: 'relative'
        }}
      >
        {services.map((service, index) => {
          const isFirst = index === 0;
          const isLast = index === services.length - 1;
          const isActive = index === activeIndex;
          
          // Premium Red Gradient or Clean White
          const bg = isActive 
            ? 'linear-gradient(135deg, #e63946 0%, #a71d2a 100%)' 
            : 'linear-gradient(135deg, #FFFFFF 0%, #f4f7fa 100%)';
            
          const textColor = isActive ? '#FFFFFF' : 'var(--color-accent)';
          
          // 3D Button Gradient for Icon
          const iconCircleBg = isActive
            ? 'linear-gradient(145deg, #ff4d5a, #c21927)'
            : 'linear-gradient(145deg, #146dc7, #0a3d75)';
          
          return (
            <motion.div 
              key={index}
              onClick={() => onServiceClick && onServiceClick(index)}
              whileHover={{ y: -8, scale: 1.02 }}
              style={{
                flex: 1,
                background: bg,
                height: '110px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                position: 'relative',
                borderTopLeftRadius: isFirst ? '100px' : '0',
                borderBottomLeftRadius: isFirst ? '100px' : '0',
                borderTopRightRadius: isLast ? '100px' : '0',
                borderBottomRightRadius: isLast ? '100px' : '0',
                paddingTop: '15px', 
                cursor: 'pointer',
                boxShadow: isActive ? 'inset 0 0 20px rgba(0,0,0,0.1)' : 'none',
                zIndex: isActive ? 5 : 1
              }}
            >
              {/* 3D Premium Icon Circle Wrapper to prevent Framer Motion from overriding the centering transform */}
              <div 
                style={{
                  position: 'absolute',
                  top: '-40px',
                  left: '50%',
                  transform: 'translateX(-50%)',
                  zIndex: 2
                }}
              >
                <motion.div 
                  whileHover={{ rotateY: 180 }}
                  transition={{ duration: 0.6, type: "spring" }}
                  style={{
                    width: '76px',
                    height: '76px',
                    borderRadius: '50%',
                    background: iconCircleBg,
                    border: '4px solid #FFFFFF',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    /* Extreme 3D Physical Button Shadows */
                    boxShadow: `
                      inset -4px -4px 8px rgba(0,0,0,0.4), 
                      inset 4px 4px 8px rgba(255,255,255,0.4), 
                      0 12px 20px rgba(0,0,0,0.2)
                    `
                  }}
                >
                  <service.icon 
                    size={32} 
                    color="#FFFFFF" 
                    strokeWidth={2} 
                    style={{ filter: 'drop-shadow(0 3px 3px rgba(0,0,0,0.5))' }}
                  />
                </motion.div>
              </div>

              {/* Title */}
              <span 
                style={{
                  color: textColor,
                  fontWeight: 800,
                  fontSize: '0.9rem',
                  lineHeight: '1.3',
                  textAlign: 'center',
                  fontFamily: 'var(--font-heading)',
                  padding: '0 10px',
                  textShadow: isActive ? '0 2px 4px rgba(0,0,0,0.4)' : 'none'
                }}
              >
                {service.title.split(' ').map((word, i) => (
                  <React.Fragment key={i}>
                    {word} <br />
                  </React.Fragment>
                ))}
              </span>
            </motion.div>
          );
        })}
      </motion.div>
    </div>
  );
}
