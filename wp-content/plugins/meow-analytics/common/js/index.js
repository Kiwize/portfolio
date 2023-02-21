const { render } = wp.element;
import { Dashboard } from './dashboard/Dashboard';

// Common Dashboard
if (!document.meowDashboardLoaded) {
	document.meowDashboardLoaded = true;
	document.addEventListener('DOMContentLoaded', function(event) {
		const commmonDash = document.getElementById('meow-common-dashboard');
		if (commmonDash) {
			render((<Dashboard />), commmonDash);
		}
	});
}

export { LicenseBlock } from './components/LicenseBlock';
