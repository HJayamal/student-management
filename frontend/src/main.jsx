import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import './index.css';

import App from './App.jsx';
import StudentApp from './StudentApp.jsx';

const isStudentPortal = window.location.pathname.startsWith('/student');

createRoot(document.getElementById('root')).render(
    <StrictMode>
        {isStudentPortal ? <StudentApp /> : <App />}
    </StrictMode>
);
