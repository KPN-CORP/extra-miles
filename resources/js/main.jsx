import React from 'react';
import { createRoot } from 'react-dom/client';
import 'remixicon/fonts/remixicon.css';
import '../css/global.css';
import AppWrapper from './app.jsx';

const rootElement = document.getElementById('root');
const root = createRoot(rootElement);

root.render(<AppWrapper />);