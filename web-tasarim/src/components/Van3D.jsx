import React, { useRef } from 'react';
import { useFrame } from '@react-three/fiber';
import { Float, MeshDistortMaterial } from '@react-three/drei';
import * as THREE from 'three';

export default function Van3D() {
  const group = useRef();

  useFrame((state) => {
    // Soft floating and rotation
    const t = state.clock.getElapsedTime();
    if (group.current) {
      group.current.rotation.y = Math.sin(t / 4) * 0.2 + 0.5;
      group.current.rotation.x = Math.cos(t / 4) * 0.1;
    }
  });

  return (
    <Float speed={2} rotationIntensity={0.5} floatIntensity={1}>
      <group ref={group} scale={[1.2, 1.2, 1.2]}>
        {/* Main Body */}
        <mesh position={[0, 0.5, 0]} castShadow>
          <boxGeometry args={[4, 1.5, 1.8]} />
          <meshPhysicalMaterial 
            color="#ffffff" 
            metalness={0.2}
            roughness={0.1}
            clearcoat={1}
            clearcoatRoughness={0.1}
          />
        </mesh>
        
        {/* Front Cabin */}
        <mesh position={[1.2, 1.5, 0]} castShadow>
          <boxGeometry args={[1.6, 1, 1.7]} />
          <meshPhysicalMaterial 
            color="#ffffff" 
            metalness={0.2}
            roughness={0.1}
            clearcoat={1}
            clearcoatRoughness={0.1}
          />
        </mesh>

        {/* Windows */}
        {/* Windshield */}
        <mesh position={[2.01, 1.5, 0]} rotation={[0, 0, -0.2]}>
          <planeGeometry args={[1, 1.5]} />
          <meshPhysicalMaterial 
            color="#0f172a" 
            metalness={0.8}
            roughness={0}
            transmission={0.9}
            thickness={0.5}
            envMapIntensity={2}
          />
        </mesh>

        {/* Side Windows */}
        <mesh position={[1.2, 1.5, 0.86]}>
          <planeGeometry args={[1.2, 0.8]} />
          <meshPhysicalMaterial 
            color="#0f172a" 
            metalness={0.8}
            roughness={0}
          />
        </mesh>
        <mesh position={[1.2, 1.5, -0.86]} rotation={[0, Math.PI, 0]}>
          <planeGeometry args={[1.2, 0.8]} />
          <meshPhysicalMaterial 
            color="#0f172a" 
            metalness={0.8}
            roughness={0}
          />
        </mesh>

        {/* Accent Line */}
        <mesh position={[0, 0.5, 0.91]}>
          <planeGeometry args={[4, 0.15]} />
          <meshBasicMaterial color="#e21b1b" />
        </mesh>
        <mesh position={[0, 0.5, -0.91]} rotation={[0, Math.PI, 0]}>
          <planeGeometry args={[4, 0.15]} />
          <meshBasicMaterial color="#e21b1b" />
        </mesh>

        {/* Wheels */}
        {[
          [-1.2, -0.2, 1], [1.2, -0.2, 1],
          [-1.2, -0.2, -1], [1.2, -0.2, -1]
        ].map((pos, idx) => (
          <group key={idx} position={pos}>
            {/* Tire */}
            <mesh rotation={[Math.PI / 2, 0, 0]} castShadow>
              <cylinderGeometry args={[0.4, 0.4, 0.3, 32]} />
              <meshStandardMaterial color="#111" roughness={0.8} />
            </mesh>
            {/* Rim */}
            <mesh rotation={[Math.PI / 2, 0, 0]} position={[0, pos[2] > 0 ? 0.16 : -0.16, 0]}>
              <cylinderGeometry args={[0.2, 0.2, 0.05, 16]} />
              <meshStandardMaterial color="#ccc" metalness={0.8} roughness={0.2} />
            </mesh>
          </group>
        ))}

        {/* Decorative Abstract Orbs around the Van (Lojistik ve teknoloji vurgusu) */}
        <Float speed={4} rotationIntensity={2} floatIntensity={2}>
          <mesh position={[-2, 2, -1]}>
            <sphereGeometry args={[0.3, 32, 32]} />
            <MeshDistortMaterial color="#e21b1b" distort={0.4} speed={3} roughness={0} />
          </mesh>
        </Float>
        <Float speed={3} rotationIntensity={1} floatIntensity={1.5}>
          <mesh position={[2, 2.5, 1]}>
            <sphereGeometry args={[0.2, 32, 32]} />
            <MeshDistortMaterial color="#10549c" distort={0.2} speed={2} roughness={0} />
          </mesh>
        </Float>
      </group>
    </Float>
  );
}
