// Error modal composable for authentication failures
export function useJobVisionError() {
  const showErrorModal = (
    message: string,
    path: string,
    originalOptions: RequestInit
  ) => {
    // Check if the error is due to an invalid/expired token
    const isTokenError = message.toLowerCase().includes('unauthenticated') || 
                         message.toLowerCase().includes('invalid_token') ||
                         message.toLowerCase().includes('authentication failed')

    // You can implement a modal display logic here
    // For now, we'll log it and provide options for token refresh
    console.log('Authentication Error:', { message, path, isTokenError })

    // In a real implementation, you would:
    // 1. Show a modal dialog with the error message
    // 2. If it's a token error, provide an option to refresh token
    // 3. If user confirms, refresh token and retry the request
    // 4. If not a token error, show a generic error message

    if (isTokenError) {
      console.log('Token error detected - would trigger token refresh')
      // Would call a function like refreshJobVisionToken() here
    }
  }

  return { showErrorModal }
}
