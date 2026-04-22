.crm-settings-form-shell {
    display: grid;
    gap: 20px;
}

.crm-settings-form-copy {
    margin: 0 0 18px;
    color: #64748b;
    font-size: 12px;
    line-height: 1.5;
}

.crm-branding-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
    align-items: start;
}

.crm-branding-card {
    border: 1px solid #e2e8f0;
    border-radius: 3px;
    padding: 20px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    display: grid;
    gap: 18px;
}

.crm-branding-card-head {
    display: grid;
    gap: 8px;
}

.crm-branding-card-head h3 {
    margin: 0;
    font-size: 18px;
    color: #0f172a;
}

.crm-branding-card-head p {
    margin: 0;
    color: #64748b;
    font-size: 12px;
    line-height: 1.5;
}

.crm-branding-trigger {
    cursor: pointer;
    display: grid;
    justify-items: center;
    gap: 12px;
    text-decoration: none;
}

.crm-branding-shell {
    width: min(100%, 220px);
    aspect-ratio: 1 / 1;
    border-radius: 3px;
    border: 1px solid #d8e4f2;
    overflow: hidden;
    background:
        radial-gradient(circle at top right, rgba(56, 189, 248, 0.16), transparent 38%),
        linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.crm-branding-trigger:hover .crm-branding-shell {
    transform: translateY(-1px);
    box-shadow: 0 16px 32px rgba(37, 99, 235, 0.14);
}

.crm-branding-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.crm-branding-placeholder {
    width: 100%;
    height: 100%;
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 14px;
    color: #1d4ed8;
    text-align: center;
    padding: 18px;
}

.crm-branding-placeholder-icon {
    position: relative;
    width: 56px;
    height: 56px;
    border-radius: 3px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.75);
    border: 1px solid #bfdbfe;
    font-size: 24px;
}

.crm-branding-placeholder-icon .crm-avatar-upload-plus {
    right: -8px;
    bottom: -8px;
}

.crm-branding-placeholder-text {
    display: grid;
    gap: 6px;
}

.crm-branding-placeholder-text strong {
    font-size: 26px;
    line-height: 1;
    color: #0f172a;
}

.crm-branding-placeholder-text span {
    color: #64748b;
    font-size: 12px;
    line-height: 1.4;
}

.crm-branding-caption {
    color: #64748b;
    font-size: 12px;
    line-height: 1.5;
    text-align: center;
}

@media (max-width: 991.98px) {
    .crm-branding-grid {
        grid-template-columns: minmax(0, 1fr);
    }
}
