import { useEffect, useRef } from 'react'

const COLORS = ['#7c6cff', '#5ac8fa', '#4ade80', '#22d3ee']
const PARTICLES = 140

export const VortexCanvas = () => {
  const ref = useRef<HTMLCanvasElement>(null)
  const frame = useRef<number>()

  useEffect(() => {
    const canvas = ref.current
    if (!canvas) return
    const ctx = canvas.getContext('2d')
    if (!ctx) return

    const dpr = Math.min(window.devicePixelRatio || 1, 2)

    const resize = () => {
      canvas.width = window.innerWidth * dpr
      canvas.height = window.innerHeight * dpr
      canvas.style.width = `${window.innerWidth}px`
      canvas.style.height = `${window.innerHeight}px`
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0)
    }

    resize()
    window.addEventListener('resize', resize)

    type Particle = {
      angle: number
      radius: number
      speed: number
      size: number
      color: string
      drift: number
    }

    const particles: Particle[] = Array.from({ length: PARTICLES }, () => ({
      angle: Math.random() * Math.PI * 2,
      radius: 80 + Math.random() * Math.min(window.innerWidth, window.innerHeight) * 0.9,
      speed: 0.002 + Math.random() * 0.003,
      size: 1 + Math.random() * 2.6,
      color: COLORS[Math.floor(Math.random() * COLORS.length)],
      drift: (Math.random() - 0.5) * 0.003,
    }))

    const center = () => ({ x: window.innerWidth / 2, y: window.innerHeight / 2 })

    const loop = () => {
      const { x: cx, y: cy } = center()
      ctx.globalCompositeOperation = 'source-over'
      ctx.fillStyle = 'rgba(6, 8, 20, 0.08)'
      ctx.fillRect(0, 0, window.innerWidth, window.innerHeight)
      ctx.globalCompositeOperation = 'lighter'

      particles.forEach((p) => {
        p.angle += p.speed
        p.radius += Math.sin(p.angle * 2) * 0.05
        const x = cx + Math.cos(p.angle + p.drift) * p.radius * 0.55
        const y = cy + Math.sin(p.angle) * p.radius * 0.45

        ctx.beginPath()
        ctx.fillStyle = p.color
        ctx.shadowColor = p.color
        ctx.shadowBlur = 14
        ctx.arc(x, y, p.size, 0, Math.PI * 2)
        ctx.fill()
      })

      frame.current = requestAnimationFrame(loop)
    }

    frame.current = requestAnimationFrame(loop)

    return () => {
      if (frame.current) cancelAnimationFrame(frame.current)
      window.removeEventListener('resize', resize)
    }
  }, [])

  return <canvas ref={ref} className="vortex-canvas" aria-hidden />
}
