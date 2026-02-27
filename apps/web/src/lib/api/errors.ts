import axios from 'axios'

export function isAbortError(error: unknown): boolean {
  if (axios.isAxiosError(error)) {
    return error.code === 'ERR_CANCELED'
  }

  return error instanceof DOMException && error.name === 'AbortError'
}

export function toErrorMessage(error: unknown): string {
  if (axios.isAxiosError(error)) {
    const payloadMessage = error.response?.data?.message

    if (typeof payloadMessage === 'string' && payloadMessage.trim().length > 0) {
      return payloadMessage
    }

    return error.message || 'Request failed.'
  }

  if (error instanceof Error) {
    return error.message
  }

  return 'Unexpected error.'
}
