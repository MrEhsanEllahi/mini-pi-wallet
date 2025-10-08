import Pusher from 'pusher-js'

let pusherInstance = null
let currentToken = null

const resolveToken = (maybeRef) => {
  if (maybeRef && typeof maybeRef === 'object' && 'value' in maybeRef) {
    return maybeRef.value
  }
  return maybeRef
}

export function initPusher (token) {
  if (pusherInstance) return pusherInstance
  currentToken = resolveToken(token) || null

  Pusher.logToConsole = true

  pusherInstance = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,

    // 🔐 Authorize private channels with your API token
    authorizer: (channel) => {
      return {
        authorize: (socketId, callback) => {
          fetch(`${import.meta.env.VITE_API_BASE_URL || ''}/broadcasting/auth`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              ...(currentToken ? { 'Authorization': `Bearer ${currentToken}` } : {})
            },
            body: JSON.stringify({
              socket_id: socketId,
              channel_name: channel.name
            })
          })
            .then(async (res) => {
              if (!res.ok) {
                const text = await res.text()
                throw new Error(`Auth failed: ${res.status} ${text}`)
              }
              return res.json()
            })
            .then((data) => callback(null, data))
            .catch((err) => callback(err, null))
        }
      }
    }
  })

  // Optional logs
  pusherInstance.connection.bind('connected', () => console.log('✅ Pusher connected'))
  pusherInstance.connection.bind('disconnected', () => console.log('❌ Pusher disconnected'))
  pusherInstance.connection.bind('error', (err) => console.error('❌ Pusher error:', err))

  return pusherInstance
}

export function updatePusherToken (token) {
  currentToken = resolveToken(token) || null
}

export function getPusher () {
  return pusherInstance || initPusher(currentToken)
}
