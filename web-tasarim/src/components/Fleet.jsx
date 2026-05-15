import React from 'react';
import { motion } from 'framer-motion';
import { Wifi, Map, Coffee, Shield } from 'lucide-react';

const fleetData = [
  {
    id: 1,
    name: "VIP Executive Van",
    type: "VIP / Protokol",
    capacity: "9 Kişi",
    image: "/images/vip_van.png",
    features: [
      { icon: <Wifi size={16}/>, label: "Sınırsız Wi-Fi" },
      { icon: <Coffee size={16}/>, label: "İkramlıklar" },
      { icon: <Shield size={16}/>, label: "Zırh Opsiyonu" }
    ],
    delay: 0.1
  },
  {
    id: 2,
    name: "Kurumsal Mekik",
    type: "Personel Taşıma",
    capacity: "16-19 Kişi",
    image: "/images/corporate_shuttle.png",
    features: [
      { icon: <Map size={16}/>, label: "Canlı Takip" },
      { icon: <Shield size={16}/>, label: "Kamera Sistemi" },
      { icon: <Wifi size={16}/>, label: "Geniş Diz Mesafesi" }
    ],
    delay: 0.3
  },
  {
    id: 3,
    name: "Güvenli Öğrenci Servisi",
    type: "Öğrenci Taşıma",
    capacity: "16-22 Kişi",
    image: "/images/student_bus.png",
    features: [
      { icon: <Shield size={16}/>, label: "3 Nokta Emniyet" },
      { icon: <Map size={16}/>, label: "Veli Bilgilendirme" },
      { icon: <Coffee size={16}/>, label: "Rehber Personel" }
    ],
    delay: 0.5
  }
];

export default function Fleet() {
  return (
    <section id="filo" className="section">
      <div style={{ maxWidth: '1200px', margin: '0 auto', position: 'relative', zIndex: 10 }}>
        <motion.div 
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6 }}
          style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', marginBottom: '64px', flexWrap: 'wrap', gap: '24px' }}
        >
          <div>
            <h2 className="section-title" style={{ textAlign: 'left', marginBottom: '8px' }}>Araç Filomuz</h2>
            <p className="section-subtitle" style={{ textAlign: 'left', margin: 0, maxWidth: '500px' }}>
              En son teknolojiyle donatılmış, düzenli bakımları yapılan ve maksimum güvenlik standartlarına sahip araçlarımız.
            </p>
          </div>
          <button className="btn-outline">Tüm Filoyu Gör</button>
        </motion.div>

        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))', gap: '40px' }}>
          {fleetData.map((vehicle) => (
            <motion.div
              key={vehicle.id}
              className="glass-panel"
              style={{ overflow: 'hidden', padding: 0 }}
              initial={{ opacity: 0, scale: 0.95 }}
              whileInView={{ opacity: 1, scale: 1 }}
              viewport={{ once: true }}
              transition={{ duration: 0.6, delay: vehicle.delay }}
              whileHover={{ y: -10 }}
            >
              <div style={{ height: '240px', overflow: 'hidden', position: 'relative' }}>
                <motion.img 
                  src={vehicle.image} 
                  alt={vehicle.name} 
                  style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                  whileHover={{ scale: 1.05 }}
                  transition={{ duration: 0.4 }}
                />
                <div style={{ position: 'absolute', top: '16px', right: '16px', background: 'rgba(0,0,0,0.6)', backdropFilter: 'blur(10px)', padding: '6px 12px', borderRadius: '20px', fontSize: '0.75rem', fontWeight: 600, border: '1px solid var(--color-border)' }}>
                  {vehicle.type}
                </div>
              </div>
              
              <div style={{ padding: '32px' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
                  <h3 style={{ fontSize: '1.25rem', m: 0 }}>{vehicle.name}</h3>
                  <span style={{ fontSize: '0.875rem', color: 'var(--color-accent)', fontWeight: 600 }}>{vehicle.capacity}</span>
                </div>
                
                <div style={{ display: 'flex', gap: '12px', flexWrap: 'wrap', marginBottom: '24px' }}>
                  {vehicle.features.map((feat, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', gap: '6px', fontSize: '0.75rem', color: 'var(--color-text-secondary)', background: 'rgba(0,0,0,0.03)', padding: '6px 12px', borderRadius: '8px', border: '1px solid rgba(0,0,0,0.05)' }}>
                      <span style={{ color: 'var(--color-accent)' }}>{feat.icon}</span>
                      {feat.label}
                    </div>
                  ))}
                </div>
                
                <button className="btn-outline" style={{ width: '100%', padding: '10px' }}>Teknik Özellikler</button>
              </div>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
}
