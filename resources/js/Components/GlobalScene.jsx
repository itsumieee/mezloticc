import { Suspense, useEffect, useMemo, useRef } from 'react';
import { Canvas, useFrame, useThree } from '@react-three/fiber';
import { Environment } from '@react-three/drei';
import * as THREE from 'three';

const SCROLL_PATH = [
    { at: 0, x: 0.44, y: 0.38, scale: 0.112 },
    { at: 0.28, x: 0.18, y: -0.45, scale: 0.072 },
    { at: 0.7, x: 0.16, y: -0.42, scale: 0.069 },
    { at: 1, x: 0.32, y: -0.35, scale: 0.072 },
];

const COMPACT_SCROLL_PATH = [
    { at: 0, x: 0.38, y: 0.15, scale: 0.08 },
    { at: 0.28, x: 0.3, y: -0.48, scale: 0.064 },
    { at: 0.7, x: 0.28, y: -0.4, scale: 0.061 },
    { at: 1, x: 0.36, y: -0.22, scale: 0.064 },
];

const PHONE_SCROLL_PATH = [
    { at: 0, x: 0.62, y: -0.25, scale: 0.072 },
    { at: 0.28, x: 0.58, y: -0.45, scale: 0.061 },
    { at: 0.7, x: 0.54, y: -0.4, scale: 0.059 },
    { at: 1, x: 0.62, y: -0.26, scale: 0.061 },
];

function Relic({ reducedMotion }) {
    const viewport = useThree((state) => state.viewport);
    const screenWidth = useThree((state) => state.size.width);
    const mesh = useRef(null);
    const material = useRef(null);
    const pointer = useRef(new THREE.Vector2());
    const scroll = useRef(0);
    const baseColor = useMemo(() => new THREE.Color('#789aa5'), []);
    const accentColor = useMemo(() => new THREE.Color('#00aebe'), []);
    const geometry = useMemo(() => new THREE.TorusKnotGeometry(1.15, 0.34, 112, 14, 2, 3), []);
    const compact = screenWidth < 1024;
    const path = screenWidth < 500
        ? PHONE_SCROLL_PATH
        : compact
            ? COMPACT_SCROLL_PATH
            : SCROLL_PATH;
    const getTargets = (progress) => {
        const nextIndex = path.findIndex((point) => point.at >= progress);
        const from = path[Math.max(0, nextIndex - 1)];
        const to = path[Math.max(0, nextIndex)];
        const segmentProgress = nextIndex <= 0 ? 0 : THREE.MathUtils.clamp(
            (progress - from.at) / (to.at - from.at),
            0,
            1,
        );

        return {
            x: THREE.MathUtils.lerp(from.x, to.x, segmentProgress) * viewport.width * 0.5,
            y: THREE.MathUtils.lerp(from.y, to.y, segmentProgress) * viewport.height * 0.5,
            scale: THREE.MathUtils.lerp(from.scale, to.scale, segmentProgress) * viewport.height,
        };
    };

    useEffect(() => {
        const node = mesh.current;
        if (!node) return;

        const maxScroll = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
        const initial = getTargets(THREE.MathUtils.clamp(window.scrollY / maxScroll, 0, 1));
        node.position.set(initial.x, initial.y, 0);
        node.scale.setScalar(initial.scale);
    }, [screenWidth, viewport.width, viewport.height]);

    useEffect(() => {
        const trackPointer = (event) => {
            pointer.current.set(
                (event.clientX / window.innerWidth) * 2 - 1,
                1 - (event.clientY / window.innerHeight) * 2,
            );
        };

        if (reducedMotion) return undefined;
        window.addEventListener('pointermove', trackPointer, { passive: true });
        return () => window.removeEventListener('pointermove', trackPointer);
    }, [reducedMotion]);

    useFrame((state, delta) => {
        const node = mesh.current;
        if (!node) return;

        const scrollRange = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
        const progress = THREE.MathUtils.clamp(window.scrollY / scrollRange, 0, 1);
        scroll.current = THREE.MathUtils.damp(scroll.current, progress, 1.5, delta);

        const section = scroll.current;
        const drift = state.clock.elapsedTime;
        const targets = getTargets(section);
        const cursorFactor = reducedMotion ? 0 : compact ? 0.035 : 0.1;
        const idleX = reducedMotion ? 0 : Math.sin(drift * 0.24) * 0.035;
        const idleY = reducedMotion ? 0 : Math.cos(drift * 0.2) * 0.045;
        const targetX = targets.x + pointer.current.x * cursorFactor + idleX;
        const targetY = targets.y + pointer.current.y * cursorFactor + idleY;

        node.position.x = THREE.MathUtils.damp(node.position.x, targetX, 1.5, delta);
        node.position.y = THREE.MathUtils.damp(node.position.y, targetY, 1.5, delta);
        const pointerTilt = reducedMotion ? 0 : compact ? 0.025 : 0.06;
        const targetRotationX = Math.sin(drift * 0.24) * 0.16 + pointer.current.y * pointerTilt;
        const targetRotationY = Math.cos(drift * 0.2) * 0.2 + pointer.current.x * pointerTilt;
        node.rotation.x = THREE.MathUtils.damp(node.rotation.x, targetRotationX, 1.4, delta);
        node.rotation.y = THREE.MathUtils.damp(node.rotation.y, targetRotationY, 1.4, delta);
        node.rotation.z = THREE.MathUtils.damp(
            node.rotation.z,
            section * Math.PI * 1.3 + (reducedMotion ? 0 : Math.sin(drift * 0.16) * 0.08),
            1.5,
            delta,
        );

        const phase = Math.sin(section * Math.PI);
        const scale = targets.scale * (1 + phase * 0.04);
        node.scale.x = THREE.MathUtils.damp(node.scale.x, scale * (1 + phase * 0.07), 1.8, delta);
        node.scale.y = THREE.MathUtils.damp(node.scale.y, scale * (1 - phase * 0.06), 1.8, delta);
        node.scale.z = THREE.MathUtils.damp(node.scale.z, scale, 1.8, delta);

        if (material.current) material.current.color.lerpColors(baseColor, accentColor, phase * 0.48);
    });

    return (
        <mesh ref={mesh} geometry={geometry} position={[0, 0, 0]} scale={0.1}>
            <meshPhysicalMaterial
                ref={material}
                thickness={0.65}
                roughness={0.08}
                metalness={0.8}
                iridescence={0.92}
                iridescenceIOR={1.35}
                iridescenceThicknessRange={[180, 850]}
                transmission={0.05}
                ior={1.38}
                color="#789aa5"
                attenuationColor="#75d5df"
                attenuationDistance={1.8}
                clearcoat={1}
                clearcoatRoughness={0.045}
                emissive="#064451"
                emissiveIntensity={0.18}
            />
        </mesh>
    );
}

function SceneContent({ reducedMotion }) {
    return (
        <>
            <Suspense fallback={null}>
                <Environment resolution={128}>
                    <mesh scale={100}>
                        <sphereGeometry args={[1, 32, 32]} />
                        <meshBasicMaterial color="#a9bac0" side={THREE.BackSide} />
                    </mesh>
                    <mesh position={[0, 2, -5]}>
                        <planeGeometry args={[3, 5]} />
                        <meshBasicMaterial color="#ffffff" side={THREE.DoubleSide} />
                    </mesh>
                    <mesh position={[5, 1, 0]} rotation={[0, -Math.PI / 2, 0]}>
                        <planeGeometry args={[2, 4]} />
                        <meshBasicMaterial color="#00c8df" side={THREE.DoubleSide} />
                    </mesh>
                    <mesh position={[-5, 1, 0]} rotation={[0, Math.PI / 2, 0]}>
                        <planeGeometry args={[2, 4]} />
                        <meshBasicMaterial color="#e5f1f4" side={THREE.DoubleSide} />
                    </mesh>
                </Environment>
            </Suspense>
            <ambientLight intensity={0.72} />
            <directionalLight position={[-4, 5, 5]} intensity={3.4} color="#ffffff" />
            <pointLight position={[3, -2, 3]} intensity={5.5} color="#00b9cb" />
            <pointLight position={[-3, 1, 2]} intensity={2.2} color="#d9f7ff" />
            <Relic reducedMotion={reducedMotion} />
        </>
    );
}

export default function GlobalScene({ reducedMotion = false }) {
    return (
        <div className="scene-root" aria-hidden="true">
            <Canvas
                camera={{ position: [0, 0, 5.8], fov: 42 }}
                dpr={[1, 1.25]}
                frameloop="always"
                gl={{ alpha: true, antialias: false, powerPreference: 'low-power' }}
                fallback={<div className="scene-static" />}
            >
                <SceneContent reducedMotion={reducedMotion} />
            </Canvas>
        </div>
    );
}
