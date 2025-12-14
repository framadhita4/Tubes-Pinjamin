import './bootstrap';
import { showToast, fetchRequest, setButtonLoading, submitForm } from './helpers/fetch';
import './lucide'; // Initialize Lucide icons

// Make helpers available globally
window.showToast = showToast;
window.fetchRequest = fetchRequest;
window.setButtonLoading = setButtonLoading;
window.submitForm = submitForm;
