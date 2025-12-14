/**
 * Fetch Helper - Centralized HTTP request handler
 * Handles CSRF tokens, JSON responses, and error handling
 */

/**
 * Show toast notification
 * @param {string} message - The message to display
 * @param {string} type - Type of toast: 'success', 'error', 'info', 'warning'
 * @param {number} duration - Duration in milliseconds (default: 3000)
 */
export function showToast(message, type = 'info', duration = 3000) {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    
    const toastTypes = {
        success: 'alert-success',
        error: 'alert-error',
        info: 'alert-info',
        warning: 'alert-warning'
    };

    const icons = {
        success: '<i data-lucide="check-circle" class="w-5 h-5"></i>',
        error: '<i data-lucide="alert-circle" class="w-5 h-5"></i>',
        info: '<i data-lucide="info" class="w-5 h-5"></i>',
        warning: '<i data-lucide="alert-triangle" class="w-5 h-5"></i>'
    };

    const toast = document.createElement('div');
    toast.className = `alert ${toastTypes[type] || toastTypes.info} shadow-lg`;
    toast.innerHTML = `
        <div class="flex items-center gap-2">
            ${icons[type] || icons.info}
            <span>${message}</span>
        </div>
    `;

    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

/**
 * Create toast container if it doesn't exist
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast toast-top toast-end z-50';
    document.body.appendChild(container);
    return container;
}

/**
 * Get CSRF token from meta tag
 */
function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) {
        console.warn('CSRF token not found');
    }
    return token;
}

/**
 * Centralized fetch helper
 * @param {string} url - The URL to fetch
 * @param {Object} options - Fetch options
 * @param {string} options.method - HTTP method (GET, POST, PUT, DELETE, etc.)
 * @param {Object|FormData} options.body - Request body
 * @param {Object} options.headers - Additional headers
 * @param {Function} options.onSuccess - Success callback
 * @param {Function} options.onError - Error callback
 * @param {boolean} options.showSuccessToast - Show success toast (default: true)
 * @param {boolean} options.showErrorToast - Show error toast (default: true)
 * @returns {Promise}
 */
export async function fetchRequest(url, options = {}) {
    const {
        method = 'GET',
        body = null,
        headers = {},
        onSuccess = null,
        onError = null,
        showSuccessToast = true,
        showErrorToast = true,
    } = options;

    try {
        // Prepare headers
        const requestHeaders = {
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
            ...headers
        };

        // Don't set Content-Type for FormData (browser will set it automatically with boundary)
        if (!(body instanceof FormData)) {
            requestHeaders['Content-Type'] = 'application/json';
        }

        // Prepare fetch options
        const fetchOptions = {
            method,
            headers: requestHeaders,
        };

        // Add body if not GET or HEAD
        if (body && method !== 'GET' && method !== 'HEAD') {
            fetchOptions.body = body instanceof FormData ? body : JSON.stringify(body);
        }

        // Make the request
        const response = await fetch(url, fetchOptions);
        const data = await response.json();

        // Handle response
        if (data.success) {
            if (showSuccessToast && data.message) {
                showToast(data.message, 'success');
            }
            
            if (onSuccess) {
                await onSuccess(data);
            }
            
            return { success: true, data };
        } else {
            // Handle error response
            if (showErrorToast) {
                if (data.errors) {
                    // Handle validation errors
                    Object.values(data.errors).flat().forEach(error => {
                        showToast(error, 'error', 4000);
                    });
                } else if (data.message) {
                    showToast(data.message, 'error');
                }
            }
            
            if (onError) {
                await onError(data);
            }
            
            return { success: false, data };
        }
    } catch (error) {
        console.error('Fetch error:', error);
        
        if (showErrorToast) {
            showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
        }
        
        if (onError) {
            await onError({ message: error.message });
        }
        
        return { success: false, error };
    }
}

/**
 * Helper function to set button loading state
 * @param {HTMLButtonElement} button - The button element
 * @param {boolean} isLoading - Loading state
 * @param {string} loadingText - Text to show when loading (default: 'Memproses...')
 */
export function setButtonLoading(button, isLoading, loadingText = 'Memproses...') {
    if (!button) return;
    
    if (isLoading) {
        button.dataset.originalContent = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `<span class="loading loading-spinner loading-sm"></span> ${loadingText}`;
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalContent || button.innerHTML;
        delete button.dataset.originalContent;
    }
}

/**
 * Helper function to handle form submission with fetch
 * @param {HTMLFormElement} form - The form element
 * @param {string} url - The URL to submit to
 * @param {Object} options - Additional options (same as fetchRequest)
 */
export async function submitForm(form, url, options = {}) {
    if (!form) return;
    
    const submitButton = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);
    
    setButtonLoading(submitButton, true, options.loadingText || 'Memproses...');
    
    const result = await fetchRequest(url, {
        method: 'POST',
        body: formData,
        ...options,
        onSuccess: async (data) => {
            if (options.onSuccess) {
                await options.onSuccess(data);
            }
            setButtonLoading(submitButton, false);
        },
        onError: async (data) => {
            if (options.onError) {
                await options.onError(data);
            }
            setButtonLoading(submitButton, false);
        }
    });
    
    if (!result.success) {
        setButtonLoading(submitButton, false);
    }
    
    return result;
}

// Make functions available globally for inline event handlers
if (typeof window !== 'undefined') {
    window.showToast = showToast;
    window.fetchRequest = fetchRequest;
    window.setButtonLoading = setButtonLoading;
    window.submitForm = submitForm;
}

