/* =========================================================================
   Heritage Pro — Colors & Type Tokens
   School management platform. Professional, trustworthy, modern.
   Brand anchor: deep indigo gradient (#434DB0) from the H logo.
   ========================================================================= */

/* ---------- Fonts ------------------------------------------------------- */
/* Primary: Plus Jakarta Sans (friendly modern sans for UI + display).
   Body alt: Inter (fallback for long-form, dense data).
   Mono: JetBrains Mono (for code / IDs / reports). */
/* cyrillic-ext */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("70cc714b-0a98-419a-829d-c9ddd181662d") format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C8A, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* cyrillic */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("dab66141-604b-45e0-98d4-bfde42674236") format('woff2');
  unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
}
/* greek-ext */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("7472dd43-ec26-4658-953a-10d8e7939387") format('woff2');
  unicode-range: U+1F00-1FFF;
}
/* greek */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("ef15c387-9c67-4912-9206-0aca7a8cb0e7") format('woff2');
  unicode-range: U+0370-0377, U+037A-037F, U+0384-038A, U+038C, U+038E-03A1, U+03A3-03FF;
}
/* vietnamese */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("b6f70e22-3f28-4a66-934f-6bc3e759b08b") format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("4b41653f-749a-4705-8dee-1f5bff20c951") format('woff2');
  unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("774be415-f311-468f-bd97-70ca67aaef8a") format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* cyrillic-ext */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("70cc714b-0a98-419a-829d-c9ddd181662d") format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C8A, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* cyrillic */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("dab66141-604b-45e0-98d4-bfde42674236") format('woff2');
  unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
}
/* greek-ext */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("7472dd43-ec26-4658-953a-10d8e7939387") format('woff2');
  unicode-range: U+1F00-1FFF;
}
/* greek */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("ef15c387-9c67-4912-9206-0aca7a8cb0e7") format('woff2');
  unicode-range: U+0370-0377, U+037A-037F, U+0384-038A, U+038C, U+038E-03A1, U+03A3-03FF;
}
/* vietnamese */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("b6f70e22-3f28-4a66-934f-6bc3e759b08b") format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("4b41653f-749a-4705-8dee-1f5bff20c951") format('woff2');
  unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("774be415-f311-468f-bd97-70ca67aaef8a") format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* cyrillic-ext */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url("70cc714b-0a98-419a-829d-c9ddd181662d") format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C8A, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* cyrillic */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url("dab66141-604b-45e0-98d4-bfde42674236") format('woff2');
  unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
}
/* greek-ext */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url("7472dd43-ec26-4658-953a-10d8e7939387") format('woff2');
  unicode-range: U+1F00-1FFF;
}
/* greek */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url("ef15c387-9c67-4912-9206-0aca7a8cb0e7") format('woff2');
  unicode-range: U+0370-0377, U+037A-037F, U+0384-038A, U+038C, U+038E-03A1, U+03A3-03FF;
}
/* vietnamese */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url("b6f70e22-3f28-4a66-934f-6bc3e759b08b") format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url("4b41653f-749a-4705-8dee-1f5bff20c951") format('woff2');
  unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url("774be415-f311-468f-bd97-70ca67aaef8a") format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* cyrillic-ext */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: url("70cc714b-0a98-419a-829d-c9ddd181662d") format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C8A, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* cyrillic */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: url("dab66141-604b-45e0-98d4-bfde42674236") format('woff2');
  unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
}
/* greek-ext */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: url("7472dd43-ec26-4658-953a-10d8e7939387") format('woff2');
  unicode-range: U+1F00-1FFF;
}
/* greek */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: url("ef15c387-9c67-4912-9206-0aca7a8cb0e7") format('woff2');
  unicode-range: U+0370-0377, U+037A-037F, U+0384-038A, U+038C, U+038E-03A1, U+03A3-03FF;
}
/* vietnamese */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: url("b6f70e22-3f28-4a66-934f-6bc3e759b08b") format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: url("4b41653f-749a-4705-8dee-1f5bff20c951") format('woff2');
  unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: url("774be415-f311-468f-bd97-70ca67aaef8a") format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* cyrillic-ext */
@font-face {
  font-family: 'JetBrains Mono';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("0b50c543-e315-4849-b081-db8e58584394") format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C8A, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* cyrillic */
@font-face {
  font-family: 'JetBrains Mono';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("9647c202-dd92-4831-88b0-f8ff64c617c3") format('woff2');
  unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
}
/* greek */
@font-face {
  font-family: 'JetBrains Mono';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("c22dacc0-d6ac-4059-af5f-f083ad2796b6") format('woff2');
  unicode-range: U+0370-0377, U+037A-037F, U+0384-038A, U+038C, U+038E-03A1, U+03A3-03FF;
}
/* vietnamese */
@font-face {
  font-family: 'JetBrains Mono';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("cc24436b-4fea-4db9-b09c-d3d88b4757ff") format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'JetBrains Mono';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("7b2a78a4-7347-4294-af9e-cb7a8528cd2f") format('woff2');
  unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'JetBrains Mono';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("709903ea-9107-4057-ac02-ceedd1e510ec") format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* cyrillic-ext */
@font-face {
  font-family: 'JetBrains Mono';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("0b50c543-e315-4849-b081-db8e58584394") format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C8A, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* cyrillic */
@font-face {
  font-family: 'JetBrains Mono';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("9647c202-dd92-4831-88b0-f8ff64c617c3") format('woff2');
  unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
}
/* greek */
@font-face {
  font-family: 'JetBrains Mono';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("c22dacc0-d6ac-4059-af5f-f083ad2796b6") format('woff2');
  unicode-range: U+0370-0377, U+037A-037F, U+0384-038A, U+038C, U+038E-03A1, U+03A3-03FF;
}
/* vietnamese */
@font-face {
  font-family: 'JetBrains Mono';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("cc24436b-4fea-4db9-b09c-d3d88b4757ff") format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'JetBrains Mono';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("7b2a78a4-7347-4294-af9e-cb7a8528cd2f") format('woff2');
  unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'JetBrains Mono';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("709903ea-9107-4057-ac02-ceedd1e510ec") format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* cyrillic-ext */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("0604ff68-c304-472b-acdc-d2e98c8c531e") format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C8A, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* vietnamese */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("41dd2368-fc7e-4c9f-9b1a-83ce9c5873d9") format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("058698a7-c73e-42e4-966d-181d832bc533") format('woff2');
  unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("e8ece59c-6888-49f4-a89e-9a85933250a3") format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* cyrillic-ext */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("0604ff68-c304-472b-acdc-d2e98c8c531e") format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C8A, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* vietnamese */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("41dd2368-fc7e-4c9f-9b1a-83ce9c5873d9") format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("058698a7-c73e-42e4-966d-181d832bc533") format('woff2');
  unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("e8ece59c-6888-49f4-a89e-9a85933250a3") format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* cyrillic-ext */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url("0604ff68-c304-472b-acdc-d2e98c8c531e") format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C8A, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* vietnamese */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url("41dd2368-fc7e-4c9f-9b1a-83ce9c5873d9") format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url("058698a7-c73e-42e4-966d-181d832bc533") format('woff2');
  unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url("e8ece59c-6888-49f4-a89e-9a85933250a3") format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* cyrillic-ext */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: url("0604ff68-c304-472b-acdc-d2e98c8c531e") format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C8A, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* vietnamese */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: url("41dd2368-fc7e-4c9f-9b1a-83ce9c5873d9") format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: url("058698a7-c73e-42e4-966d-181d832bc533") format('woff2');
  unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: url("e8ece59c-6888-49f4-a89e-9a85933250a3") format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
/* cyrillic-ext */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 800;
  font-display: swap;
  src: url("0604ff68-c304-472b-acdc-d2e98c8c531e") format('woff2');
  unicode-range: U+0460-052F, U+1C80-1C8A, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
}
/* vietnamese */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 800;
  font-display: swap;
  src: url("41dd2368-fc7e-4c9f-9b1a-83ce9c5873d9") format('woff2');
  unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+1EA0-1EF9, U+20AB;
}
/* latin-ext */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 800;
  font-display: swap;
  src: url("058698a7-c73e-42e4-966d-181d832bc533") format('woff2');
  unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
}
/* latin */
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-style: normal;
  font-weight: 800;
  font-display: swap;
  src: url("e8ece59c-6888-49f4-a89e-9a85933250a3") format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}


:root {
  /* ===== Brand ========================================================= */
  --brand-indigo-50:  #EEF0FB;
  --brand-indigo-100: #DCE0F5;
  --brand-indigo-200: #B8C0EC;
  --brand-indigo-300: #8B95DE;
  --brand-indigo-400: #6570CF;
  --brand-indigo-500: #434DB0;   /* primary — from logo */
  --brand-indigo-600: #3B43A0;
  --brand-indigo-700: #323A8C;
  --brand-indigo-800: #262C6E;
  --brand-indigo-900: #1B2055;

  --brand-primary: var(--brand-indigo-500);
  --brand-primary-hover: var(--brand-indigo-600);
  --brand-primary-press: var(--brand-indigo-700);
  --brand-gradient: linear-gradient(135deg, #4E58C6 0%, #434DB0 55%, #363FA0 100%);
  --brand-gradient-soft: linear-gradient(135deg, #EEF0FB 0%, #DCE0F5 100%);

  /* ===== Accent (report-card gold, used sparingly for badges, awards, gamification) */
  --accent-gold-50:  #FFF8E6;
  --accent-gold-100: #FFECB8;
  --accent-gold-300: #FFD166;
  --accent-gold-500: #E5A829;
  --accent-gold-700: #A8791B;

  /* ===== Semantic ====================================================== */
  --success-50:  #E8F7EF;
  --success-100: #C8EBD6;
  --success-500: #2AA870;
  --success-600: #1F8F5E;
  --success-700: #166E49;

  --warning-50:  #FFF4E0;
  --warning-100: #FFE3B3;
  --warning-500: #E69414;
  --warning-600: #BF7A0F;

  --danger-50:  #FDECEC;
  --danger-100: #F9C9C9;
  --danger-500: #D94646;
  --danger-600: #B73535;
  --danger-700: #8E2626;

  --info-50:  #E8F1FD;
  --info-100: #C6DDF8;
  --info-500: #2E7AE0;
  --info-600: #2563BF;

  /* ===== Neutrals (slate) ============================================== */
  --neutral-0:   #FFFFFF;
  --neutral-25:  #FBFCFD;
  --neutral-50:  #F5F7FA;
  --neutral-100: #EDF0F5;
  --neutral-150: #E2E7EF;
  --neutral-200: #D3D9E3;
  --neutral-300: #B3BBC9;
  --neutral-400: #8C95A8;
  --neutral-500: #6B7385;
  --neutral-600: #4E5566;
  --neutral-700: #373D4B;
  --neutral-800: #242936;
  --neutral-900: #141822;

  /* ===== Foreground / Background semantic =============================== */
  --fg-1: var(--neutral-900);   /* headlines */
  --fg-2: var(--neutral-700);   /* body */
  --fg-3: var(--neutral-500);   /* secondary / helper */
  --fg-4: var(--neutral-400);   /* tertiary / placeholder */
  --fg-on-brand: #FFFFFF;
  --fg-link: var(--brand-indigo-500);

  --bg-page:      #F7F8FB;   /* app chrome */
  --bg-surface:   #FFFFFF;   /* cards / panels */
  --bg-subtle:    var(--neutral-50);
  --bg-muted:     var(--neutral-100);
  --bg-inverse:   var(--neutral-900);
  --bg-brand-tint: var(--brand-indigo-50);

  --border-1: var(--neutral-150);   /* default dividers, card border */
  --border-2: var(--neutral-200);   /* input border */
  --border-3: var(--neutral-300);   /* stronger */
  --border-focus: var(--brand-indigo-500);

  /* ===== Typography ==================================================== */
  --font-sans: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  --font-display: 'Plus Jakarta Sans', 'Inter', sans-serif;
  --font-body: 'Inter', 'Plus Jakarta Sans', sans-serif;
  --font-mono: 'JetBrains Mono', ui-monospace, Menlo, monospace;

  /* Fluid type scale — desktop-first but tightens on narrow viewports */
  --text-xs:   12px;
  --text-sm:   14px;
  --text-base: 16px;
  --text-md:   17px;
  --text-lg:   19px;
  --text-xl:   22px;
  --text-2xl:  28px;
  --text-3xl:  34px;
  --text-4xl:  44px;
  --text-5xl:  56px;
  --text-6xl:  72px;

  --leading-tight: 1.15;
  --leading-snug:  1.3;
  --leading-normal: 1.5;
  --leading-relaxed: 1.65;

  --tracking-tight: -0.02em;
  --tracking-snug:  -0.01em;
  --tracking-normal: 0;
  --tracking-wide: 0.02em;
  --tracking-caps: 0.08em;

  /* ===== Spacing (4px base) ============================================ */
  --space-0: 0;
  --space-1: 4px;
  --space-2: 8px;
  --space-3: 12px;
  --space-4: 16px;
  --space-5: 20px;
  --space-6: 24px;
  --space-8: 32px;
  --space-10: 40px;
  --space-12: 48px;
  --space-16: 64px;
  --space-20: 80px;
  --space-24: 96px;
  --space-32: 128px;

  /* ===== Radii ========================================================= */
  --radius-xs: 4px;
  --radius-sm: 6px;
  --radius-md: 10px;
  --radius-lg: 14px;
  --radius-xl: 20px;
  --radius-2xl: 28px;
  --radius-pill: 999px;

  /* ===== Shadows (soft, layered — never harsh) ======================== */
  --shadow-xs: 0 1px 2px rgba(20, 24, 34, 0.05);
  --shadow-sm: 0 1px 3px rgba(20, 24, 34, 0.06), 0 1px 2px rgba(20, 24, 34, 0.04);
  --shadow-md: 0 4px 10px -2px rgba(20, 24, 34, 0.08), 0 2px 4px -2px rgba(20, 24, 34, 0.04);
  --shadow-lg: 0 12px 24px -6px rgba(20, 24, 34, 0.10), 0 4px 8px -4px rgba(20, 24, 34, 0.06);
  --shadow-xl: 0 24px 48px -12px rgba(20, 24, 34, 0.16);
  --shadow-brand: 0 10px 24px -8px rgba(67, 77, 176, 0.45);

  /* Inner/inset for fields */
  --shadow-inset: inset 0 1px 2px rgba(20, 24, 34, 0.05);

  /* Focus ring */
  --ring-focus: 0 0 0 3px rgba(67, 77, 176, 0.28);

  /* ===== Motion ======================================================== */
  --ease-out: cubic-bezier(0.22, 1, 0.36, 1);
  --ease-in-out: cubic-bezier(0.65, 0, 0.35, 1);
  --dur-fast: 120ms;
  --dur-base: 200ms;
  --dur-slow: 360ms;

  /* ===== Layout ======================================================== */
  --container-narrow: 780px;
  --container-base:   1200px;
  --container-wide:   1400px;
}

/* =========================================================================
   Semantic element styles — consumable directly by simple HTML or used as
   a reference for component styles.
   ========================================================================= */
html { font-size: 16px; }

body {
  font-family: var(--font-body);
  color: var(--fg-2);
  background: var(--bg-page);
  font-size: var(--text-base);
  line-height: var(--leading-normal);
  -webkit-font-smoothing: antialiased;
  text-rendering: optimizeLegibility;
}

.display, h1.display {
  font-family: var(--font-display);
  font-weight: 800;
  font-size: var(--text-6xl);
  line-height: var(--leading-tight);
  letter-spacing: var(--tracking-tight);
  color: var(--fg-1);
}

h1 {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: var(--text-5xl);
  line-height: var(--leading-tight);
  letter-spacing: var(--tracking-tight);
  color: var(--fg-1);
}

h2 {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: var(--text-3xl);
  line-height: var(--leading-snug);
  letter-spacing: var(--tracking-snug);
  color: var(--fg-1);
}

h3 {
  font-family: var(--font-display);
  font-weight: 600;
  font-size: var(--text-2xl);
  line-height: var(--leading-snug);
  color: var(--fg-1);
}

h4 {
  font-family: var(--font-display);
  font-weight: 600;
  font-size: var(--text-xl);
  line-height: var(--leading-snug);
  color: var(--fg-1);
}

h5 {
  font-family: var(--font-display);
  font-weight: 600;
  font-size: var(--text-lg);
  color: var(--fg-1);
}

.eyebrow {
  font-family: var(--font-sans);
  font-weight: 600;
  font-size: var(--text-xs);
  text-transform: uppercase;
  letter-spacing: var(--tracking-caps);
  color: var(--brand-indigo-500);
}

p { color: var(--fg-2); line-height: var(--leading-relaxed); }
.lead { font-size: var(--text-lg); color: var(--fg-2); line-height: var(--leading-relaxed); }
.muted { color: var(--fg-3); }
small, .small { font-size: var(--text-sm); color: var(--fg-3); }
.caption { font-size: var(--text-xs); color: var(--fg-3); }

a { color: var(--fg-link); text-decoration: none; font-weight: 500; }
a:hover { text-decoration: underline; text-underline-offset: 3px; }

code, kbd, samp, pre, .mono {
  font-family: var(--font-mono);
  font-size: 0.92em;
}

code {
  background: var(--bg-muted);
  padding: 0.12em 0.4em;
  border-radius: var(--radius-xs);
  color: var(--neutral-800);
}
</style>
<style>/* =========================================================================
   Heritage Pro — Website
   Full marketing site; formal corporate tone; institutional + product-led.
   ========================================================================= */

* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; }
body {
  font-family: var(--font-body);
  color: var(--fg-1);
  background: #F7F8FB;
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
}

img { display: block; max-width: 100%; }
a { color: var(--brand-indigo-500); text-decoration: none; }
a:hover { text-decoration: underline; }

/* ---------- Shared ---------- */
.container { max-width: 1200px; margin: 0 auto; padding: 0 32px; }
.container-narrow { max-width: 820px; margin: 0 auto; padding: 0 32px; }
.eyebrow { display: inline-block; text-transform: uppercase; letter-spacing: 0.12em; font-size: 12px; font-weight: 700; color: var(--brand-indigo-500); }
.eyebrow.muted { color: var(--fg-3); }
h1, h2, h3, h4, h5 { font-family: var(--font-display); letter-spacing: -0.02em; color: var(--fg-1); margin: 0; }
h1 { font-size: 64px; line-height: 1.05; font-weight: 800; }
h2 { font-size: 44px; line-height: 1.1; font-weight: 700; }
h3 { font-size: 28px; line-height: 1.2; font-weight: 700; }
h4 { font-size: 20px; line-height: 1.3; font-weight: 600; }
p  { margin: 0 0 16px; color: var(--fg-2); }
p.lead { font-size: 18px; color: var(--fg-3); line-height: 1.55; }
.section { padding: 112px 0; }
.section-sm { padding: 72px 0; }
.center { text-align: center; }
.muted { color: var(--fg-3); }

/* ---------- Buttons ---------- */
.btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 11px 20px; border-radius: 10px; font-weight: 600; font-size: 14px;
  border: 1px solid transparent; cursor: pointer; font-family: inherit;
  transition: all 180ms var(--ease-out); text-decoration: none;
}
.btn:hover { text-decoration: none; }
.btn-primary { background: var(--brand-indigo-500); color: #fff; }
.btn-primary:hover { background: var(--brand-indigo-600); box-shadow: var(--shadow-brand); transform: translateY(-1px); }
.btn-secondary { background: transparent; color: var(--fg-1); border-color: var(--border-2); }
.btn-secondary:hover { border-color: var(--brand-indigo-500); color: var(--brand-indigo-600); }
.btn-ghost { background: transparent; color: var(--fg-2); }
.btn-ghost:hover { color: var(--brand-indigo-500); }
.btn-lg { padding: 14px 24px; font-size: 15px; }
.btn-white { background: #fff; color: var(--brand-indigo-600); }
.btn-white:hover { background: rgba(255,255,255,0.92); transform: translateY(-1px); }

/* ---------- Nav ---------- */
.nav {
  position: sticky; top: 0; z-index: 50;
  background: rgba(255,255,255,0.92); backdrop-filter: blur(14px);
  border-bottom: 1px solid var(--border-1);
}
.nav-inner { display: flex; align-items: center; gap: 40px; padding: 18px 0; }
.nav-logo { display: flex; align-items: center; gap: 10px; font-family: var(--font-display); font-size: 18px; font-weight: 800; color: var(--fg-1); }
.nav-logo img { width: 32px; height: 32px; }
.nav-logo b { color: var(--brand-indigo-500); }
.nav-links { display: flex; align-items: center; gap: 28px; margin-left: 12px; }
.nav-links a { color: var(--fg-2); font-size: 14px; font-weight: 500; }
.nav-links a:hover { color: var(--brand-indigo-500); }
.nav-cta { margin-left: auto; display: flex; gap: 10px; align-items: center; }

/* ---------- Hero ---------- */
.hero { padding: 72px 0 96px; background: linear-gradient(180deg, var(--brand-indigo-50) 0%, transparent 60%); }
.hero-inner { display: grid; grid-template-columns: 1.15fr 1fr; gap: 72px; align-items: center; }
.hero h1 { margin-bottom: 20px; }
.hero .lead { margin-bottom: 32px; max-width: 520px; }
.hero-cta { display: flex; gap: 12px; margin-bottom: 36px; }
.hero-trust { display: flex; gap: 24px; align-items: center; font-size: 13px; color: var(--fg-3); flex-wrap: wrap; }
.hero-trust b { font-family: var(--font-display); color: var(--fg-1); font-size: 22px; font-weight: 700; display: block; line-height: 1; margin-bottom: 2px; }

/* Hero — centred variant */
.hero.centred .hero-inner { grid-template-columns: 1fr; text-align: center; }
.hero.centred h1 { max-width: 900px; margin-inline: auto; margin-bottom: 20px; }
.hero.centred .lead { margin-inline: auto; }
.hero.centred .hero-cta { justify-content: center; }
.hero.centred .hero-trust { justify-content: center; }
.hero.centred .hero-media { margin-top: 48px; }

/* Hero — split-split variant */
.hero.split .hero-inner { grid-template-columns: 1fr 1fr; }

/* Hero — dark variant */
.hero.dark { background: var(--brand-gradient); color: #fff; padding: 96px 0 112px; }
.hero.dark h1, .hero.dark .eyebrow, .hero.dark .hero-trust b { color: #fff; }
.hero.dark .eyebrow { color: rgba(255,255,255,0.72); }
.hero.dark .lead { color: rgba(255,255,255,0.8); }
.hero.dark .hero-trust { color: rgba(255,255,255,0.6); }

/* Hero media — screenshot mock frame */
.hero-media {
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  background: #fff;
  border: 1px solid var(--border-1);
  box-shadow: 0 40px 80px -24px rgba(37, 44, 110, 0.28), 0 12px 32px -12px rgba(37, 44, 110, 0.12);
}
.hero-media .window-chrome {
  height: 32px; background: #F3F5F9; display: flex; gap: 6px;
  align-items: center; padding: 0 14px; border-bottom: 1px solid var(--border-1);
}
.hero-media .window-chrome span { width: 10px; height: 10px; border-radius: 50%; background: var(--neutral-200); }
.hero-media .window-chrome .url {
  flex: 1; margin-left: 18px; font-size: 11px; color: var(--fg-3); font-family: var(--font-mono);
  background: #fff; padding: 4px 10px; border-radius: 6px; border: 1px solid var(--border-1);
  max-width: 280px;
}

/* ---------- Mini dashboard mock (reused in multiple sections) ---------- */
.mini-dash { padding: 20px; display: grid; grid-template-columns: 200px 1fr; gap: 18px; min-height: 440px; }
.mini-side {
  background: var(--bg-subtle); border: 1px solid var(--border-1); border-radius: 10px;
  padding: 14px; display: flex; flex-direction: column; gap: 6px;
}
.mini-side-brand { display: flex; gap: 8px; align-items: center; padding-bottom: 10px; border-bottom: 1px solid var(--border-1); margin-bottom: 6px; }
.mini-tile { width: 24px; height: 24px; border-radius: 6px; background: var(--brand-gradient); flex-shrink: 0; }
.mini-side-brand b { font-family: var(--font-display); font-size: 13px; font-weight: 700; }
.mini-side-brand b span { color: var(--brand-indigo-500); }
.mini-nav-item { display: flex; align-items: center; gap: 8px; padding: 6px 8px; border-radius: 6px; font-size: 11px; color: var(--fg-3); font-weight: 500; }
.mini-nav-item.active { background: var(--brand-indigo-50); color: var(--brand-indigo-600); font-weight: 600; }
.mini-nav-item .dot { width: 10px; height: 10px; border-radius: 3px; background: var(--neutral-300); flex-shrink: 0; }
.mini-nav-item.active .dot { background: var(--brand-indigo-500); }
.mini-main { display: flex; flex-direction: column; gap: 14px; }
.mini-h { font-size: 16px; font-weight: 700; font-family: var(--font-display); color: var(--fg-1); }
.mini-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.mini-stat {
  border: 1px solid var(--border-1); border-radius: 10px; padding: 12px 14px; background: #fff;
}
.mini-stat b { display: block; font-family: var(--font-display); font-size: 18px; font-weight: 700; }
.mini-stat span { font-size: 10px; color: var(--fg-3); }
.mini-stat .badge { display: inline-block; margin-top: 4px; padding: 2px 6px; font-size: 9px; font-weight: 700; border-radius: 4px; background: var(--success-50); color: var(--success-700); }
.mini-chart {
  height: 120px; border: 1px solid var(--border-1); border-radius: 10px;
  background: #fff; padding: 12px; display: flex; align-items: flex-end; gap: 6px;
}
.mini-chart .bar { flex: 1; background: var(--brand-indigo-100); border-radius: 3px 3px 1px 1px; position: relative; overflow: hidden; }
.mini-chart .bar::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; background: var(--brand-indigo-500); border-radius: 3px 3px 1px 1px; height: 50%; }
.mini-table { border: 1px solid var(--border-1); border-radius: 10px; background: #fff; padding: 10px 14px; display: flex; flex-direction: column; gap: 6px; font-size: 11px; }
.mini-table-row { display: grid; grid-template-columns: 2fr 1fr 1fr 0.6fr; gap: 10px; padding: 6px 0; align-items: center; border-bottom: 1px solid var(--border-1); }
.mini-table-row:last-child { border: 0; }
.mini-table-row .pill { padding: 2px 6px; font-size: 9px; font-weight: 700; border-radius: 999px; background: var(--success-50); color: var(--success-700); text-align: center; }
.mini-table-row .pill.warn { background: var(--warning-50); color: var(--warning-600); }
.mini-table-row .pill.danger { background: var(--danger-50); color: var(--danger-700); }
.mini-table-row code { font-family: var(--font-mono); color: var(--neutral-700); background: var(--neutral-100); padding: 1px 5px; border-radius: 3px; font-size: 10px; }

/* ---------- Stats section ---------- */
.stats {
  background: var(--brand-indigo-900); color: #fff;
  padding: 72px 0;
}
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px; }
.stats-num { font-family: var(--font-display); font-size: 56px; font-weight: 800; line-height: 1; letter-spacing: -0.02em; margin-bottom: 8px; }
.stats-num span { color: var(--accent-gold-300); }
.stats-label { font-size: 14px; color: rgba(255,255,255,0.7); font-weight: 500; }

/* ---------- Products section ---------- */
.products { background: #fff; }
.products-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 56px; }
.product-card {
  position: relative; padding: 36px 32px; border-radius: 20px;
  background: #fff; border: 1px solid var(--border-1); box-shadow: var(--shadow-sm);
  transition: all 240ms var(--ease-out); overflow: hidden;
}
.product-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); border-color: var(--brand-indigo-300); }
.product-card .product-badge {
  position: absolute; top: 16px; right: 16px; font-size: 11px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.08em;
  padding: 4px 10px; border-radius: 999px;
}
.product-card .tile {
  width: 56px; height: 56px; border-radius: 14px; background: var(--brand-gradient);
  display: flex; align-items: center; justify-content: center; color: #fff;
  margin-bottom: 20px;
}
.product-card .tile svg { width: 28px; height: 28px; }
.product-card.schools .tile { background: var(--brand-gradient); }
.product-card.collegiate .tile { background: linear-gradient(135deg, #2AA870 0%, #166E49 100%); }
.product-card.k12 .tile { background: linear-gradient(135deg, #E69414 0%, #A8791B 100%); }
.product-card.schools .product-badge { background: var(--brand-indigo-50); color: var(--brand-indigo-600); }
.product-card.collegiate .product-badge { background: var(--success-50); color: var(--success-700); }
.product-card.k12 .product-badge { background: var(--accent-gold-50); color: var(--accent-gold-700); }
.product-card h3 { margin-bottom: 10px; }
.product-card p { color: var(--fg-3); font-size: 15px; margin-bottom: 20px; }
.product-card ul { list-style: none; padding: 0; margin: 0 0 24px; display: flex; flex-direction: column; gap: 8px; }
.product-card li {
  position: relative; padding-left: 24px; font-size: 14px; color: var(--fg-2);
}
.product-card li::before {
  content: ''; position: absolute; left: 0; top: 5px; width: 14px; height: 14px; border-radius: 50%;
  background: var(--success-50) url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%232AA870' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'><polyline points='20 6 9 17 4 12'/></svg>") center/10px no-repeat;
}

/* ---------- Features deep-dive ---------- */
.features { background: #F7F8FB; }
.features-row {
  display: grid; grid-template-columns: 1fr 1.15fr; gap: 72px;
  align-items: center; margin-top: 96px;
}
.features-row:first-of-type { margin-top: 64px; }
.features-row.reverse { grid-template-columns: 1.15fr 1fr; }
.features-row.reverse .feature-copy { order: 2; }
.features-row.reverse .feature-mock { order: 1; }
.feature-copy .eyebrow { margin-bottom: 16px; }
.feature-copy h3 { margin-bottom: 16px; }
.feature-copy p { color: var(--fg-3); font-size: 16px; line-height: 1.6; margin-bottom: 22px; }
.feature-copy ul { list-style: none; padding: 0; margin: 0 0 28px; display: flex; flex-direction: column; gap: 10px; }
.feature-copy ul li { position: relative; padding-left: 26px; font-size: 14px; color: var(--fg-2); line-height: 1.5; }
.feature-copy ul li::before {
  content: ''; position: absolute; left: 0; top: 4px; width: 16px; height: 16px; border-radius: 50%;
  background: var(--brand-indigo-50) url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23434DB0' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'><polyline points='20 6 9 17 4 12'/></svg>") center/10px no-repeat;
}
.feature-copy ul li b { color: var(--fg-1); font-weight: 600; }
.feature-mock {
  background: #fff;
  border-radius: 16px; border: 1px solid var(--border-1);
  box-shadow: 0 30px 60px -24px rgba(37, 44, 110, 0.22), 0 8px 20px -8px rgba(37, 44, 110, 0.08);
  overflow: hidden;
}

/* Feature icons grid (shorter modules) */
.modules-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 64px; }
.module-tile {
  padding: 24px; border-radius: 14px; background: #fff; border: 1px solid var(--border-1);
  transition: all 180ms var(--ease-out);
}
.module-tile:hover { border-color: var(--brand-indigo-300); box-shadow: var(--shadow-sm); transform: translateY(-2px); }
.module-tile .icon {
  width: 44px; height: 44px; border-radius: 10px; background: var(--brand-indigo-50);
  color: var(--brand-indigo-500); display: flex; align-items: center; justify-content: center;
  margin-bottom: 16px;
}
.module-tile .icon svg { width: 22px; height: 22px; }
.module-tile h4 { font-size: 15px; margin-bottom: 6px; }
.module-tile p { font-size: 13px; color: var(--fg-3); margin: 0; line-height: 1.5; }

/* ---------- Testimonials ---------- */
.testimonials { background: #fff; }
.testimonial-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 56px; }
.testimonial {
  padding: 28px; background: #fff; border: 1px solid var(--border-1); border-radius: 16px;
  display: flex; flex-direction: column;
}
.testimonial p { font-size: 15px; color: var(--fg-1); line-height: 1.6; flex: 1; }
.testimonial .author { display: flex; align-items: center; gap: 12px; padding-top: 18px; border-top: 1px solid var(--border-1); margin-top: 20px; }
.testimonial .author .avatar { width: 40px; height: 40px; border-radius: 999px; background: var(--brand-gradient); color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; font-size: 14px; }
.testimonial .author b { display: block; font-size: 14px; }
.testimonial .author span { font-size: 12px; color: var(--fg-3); }
.testimonial.featured { background: var(--brand-indigo-900); color: #fff; border: none; }
.testimonial.featured p { color: rgba(255,255,255,0.95); }
.testimonial.featured .author { border-top-color: rgba(255,255,255,0.15); }
.testimonial.featured .author b { color: #fff; }
.testimonial.featured .author span { color: rgba(255,255,255,0.6); }
.testimonial.featured .author .avatar { background: rgba(255,255,255,0.15); color: #fff; }
.testimonial .stars { color: var(--accent-gold-500); font-size: 14px; margin-bottom: 12px; letter-spacing: 2px; }
.testimonial.featured .stars { color: var(--accent-gold-300); }

/* ---------- Case studies ---------- */
.cases { background: #F7F8FB; }
.cases-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 48px; }
.case-card {
  background: #fff; border: 1px solid var(--border-1); border-radius: 16px; overflow: hidden;
  transition: all 180ms var(--ease-out); display: flex; flex-direction: column;
}
.case-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
.case-cover { height: 180px; position: relative; overflow: hidden; }
.case-cover.schools { background: linear-gradient(135deg, #4E58C6 0%, #363FA0 100%); }
.case-cover.collegiate { background: linear-gradient(135deg, #2AA870 0%, #166E49 100%); }
.case-cover.k12 { background: linear-gradient(135deg, #E69414 0%, #A8791B 100%); }
.case-cover::after {
  content: ''; position: absolute; inset: 0;
  background-image: radial-gradient(circle at 80% 30%, rgba(255,255,255,0.2) 0%, transparent 60%);
}
.case-cover .case-kicker {
  position: absolute; bottom: 16px; left: 20px; z-index: 2;
  color: #fff; font-family: var(--font-display); font-size: 20px; font-weight: 700;
}
.case-cover .case-tag {
  position: absolute; top: 16px; left: 20px; z-index: 2;
  background: rgba(255,255,255,0.2); backdrop-filter: blur(8px);
  color: #fff; font-size: 11px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.08em;
  padding: 4px 10px; border-radius: 999px;
}
.case-body { padding: 24px; flex: 1; display: flex; flex-direction: column; }
.case-body h4 { margin-bottom: 10px; }
.case-body p { color: var(--fg-3); font-size: 14px; flex: 1; }
.case-metrics { display: flex; gap: 20px; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-1); }
.case-metric b { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--brand-indigo-500); display: block; line-height: 1; }
.case-metric span { font-size: 11px; color: var(--fg-3); text-transform: uppercase; letter-spacing: 0.06em; }

/* ---------- Pricing ---------- */
.pricing { background: #fff; }
.pricing-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 56px; max-width: 1040px; margin-inline: auto; }
.price-card {
  padding: 36px 28px; border: 1px solid var(--border-1); border-radius: 20px;
  background: #fff; position: relative; text-align: left;
}
.price-card.highlight {
  border: 2px solid var(--brand-indigo-500);
  box-shadow: 0 30px 60px -24px rgba(67, 77, 176, 0.3);
  transform: translateY(-8px);
}
.price-card .ribbon {
  position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
  background: var(--brand-indigo-500); color: #fff; font-size: 11px; font-weight: 700;
  letter-spacing: 0.06em; text-transform: uppercase; padding: 5px 14px; border-radius: 999px;
}
.price-name { font-family: var(--font-display); font-size: 16px; font-weight: 700; color: var(--brand-indigo-500); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.06em; }
.price-amount { font-family: var(--font-display); font-size: 44px; font-weight: 800; color: var(--fg-1); letter-spacing: -0.02em; line-height: 1; }
.price-unit { font-size: 14px; color: var(--fg-3); font-weight: 500; margin-top: 6px; margin-bottom: 20px; }
.price-desc { font-size: 14px; color: var(--fg-2); margin-bottom: 20px; line-height: 1.5; min-height: 60px; }
.price-card ul { list-style: none; padding: 0; margin: 0 0 24px; display: flex; flex-direction: column; gap: 10px; }
.price-card ul li {
  position: relative; padding-left: 26px; font-size: 14px; color: var(--fg-2);
}
.price-card ul li::before {
  content: ''; position: absolute; left: 0; top: 4px; width: 16px; height: 16px; border-radius: 50%;
  background: var(--brand-indigo-50) url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23434DB0' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'><polyline points='20 6 9 17 4 12'/></svg>") center/10px no-repeat;
}
.price-cta { width: 100%; justify-content: center; }

/* ---------- FAQ ---------- */
.faq { background: #F7F8FB; }
.faq-list { display: flex; flex-direction: column; gap: 12px; margin-top: 40px; }
.faq-item {
  padding: 20px 24px; background: #fff; border: 1px solid var(--border-1); border-radius: 14px;
  cursor: pointer; transition: all 180ms var(--ease-out);
}
.faq-item:hover { border-color: var(--brand-indigo-300); }
.faq-item.open { box-shadow: var(--shadow-sm); border-color: var(--brand-indigo-300); }
.faq-q { display: flex; justify-content: space-between; align-items: center; gap: 16px; font-weight: 600; font-size: 15px; color: var(--fg-1); }
.faq-q svg { flex-shrink: 0; color: var(--fg-3); transition: transform 180ms var(--ease-out); }
.faq-item.open .faq-q svg { transform: rotate(180deg); color: var(--brand-indigo-500); }
.faq-a { margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-1); color: var(--fg-2); font-size: 14px; line-height: 1.6; }

/* ---------- Blog ---------- */
.blog { background: #fff; }
.blog-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 48px; }
.blog-card { display: flex; flex-direction: column; }
.blog-cover {
  height: 200px; border-radius: 12px; margin-bottom: 20px; position: relative; overflow: hidden;
  background: var(--brand-indigo-50);
}
.blog-cover.a { background: linear-gradient(135deg, #DCE0F5 0%, #B8C0EC 100%); }
.blog-cover.b { background: linear-gradient(135deg, #C8EBD6 0%, #2AA870 110%); }
.blog-cover.c { background: linear-gradient(135deg, #FFECB8 0%, #E5A829 110%); }
.blog-tag { display: inline-block; font-size: 11px; font-weight: 700; color: var(--brand-indigo-500); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px; }
.blog-card h4 { margin-bottom: 10px; }
.blog-card p { font-size: 14px; color: var(--fg-3); margin-bottom: 12px; }
.blog-meta { font-size: 12px; color: var(--fg-3); margin-top: auto; }

/* ---------- Contact / CTA strip ---------- */
.contact {
  background: var(--brand-gradient); color: #fff;
  padding: 96px 0;
}
.contact-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 72px; align-items: center; }
.contact h2 { color: #fff; margin-bottom: 16px; }
.contact p { color: rgba(255,255,255,0.82); font-size: 17px; margin-bottom: 28px; }
.contact-form {
  background: #fff; border-radius: 20px; padding: 32px; box-shadow: var(--shadow-xl);
}
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
.form-field label { font-size: 12px; font-weight: 600; color: var(--fg-2); }
.form-field input, .form-field select, .form-field textarea {
  padding: 10px 12px; border: 1px solid var(--border-2); border-radius: 10px;
  font-family: inherit; font-size: 14px; background: #fff;
}
.form-field input:focus, .form-field select:focus, .form-field textarea:focus {
  outline: none; border-color: var(--border-focus); box-shadow: var(--ring-focus);
}
.form-field textarea { resize: vertical; min-height: 80px; }
.contact-cta { width: 100%; justify-content: center; padding: 14px; font-size: 15px; }
.contact-list { list-style: none; padding: 0; margin: 32px 0 0; display: flex; flex-direction: column; gap: 14px; }
.contact-list li { display: flex; gap: 14px; align-items: flex-start; color: rgba(255,255,255,0.88); font-size: 14px; }
.contact-list li svg { flex-shrink: 0; color: var(--accent-gold-300); margin-top: 2px; }
.contact-list li b { color: #fff; display: block; margin-bottom: 2px; font-size: 14px; }

/* ---------- Logo strip (customers) ---------- */
.logo-strip { background: #fff; border-top: 1px solid var(--border-1); border-bottom: 1px solid var(--border-1); padding: 48px 0; }
.logo-strip .label { text-align: center; font-size: 12px; color: var(--fg-3); font-weight: 600; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 28px; }
.logo-row { display: flex; justify-content: space-between; align-items: center; gap: 32px; flex-wrap: wrap; }
.fake-logo { display: flex; align-items: center; gap: 10px; opacity: 0.55; transition: opacity 180ms; }
.fake-logo:hover { opacity: 0.95; }
.fake-logo .mark {
  width: 30px; height: 30px; border-radius: 7px;
  background: var(--neutral-700);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 800; font-size: 13px; font-family: var(--font-display);
}
.fake-logo span { font-family: var(--font-display); font-weight: 700; font-size: 15px; color: var(--neutral-700); white-space: nowrap; }

/* ---------- Footer ---------- */
.footer { background: #0F1220; color: rgba(255,255,255,0.7); padding: 72px 0 32px; }
.footer-top { display: grid; grid-template-columns: 1.3fr 2fr; gap: 64px; padding-bottom: 48px; border-bottom: 1px solid rgba(255,255,255,0.08); }
.footer-brand .nav-logo { color: #fff; margin-bottom: 18px; }
.footer-brand .nav-logo b { color: var(--accent-gold-300); }
.footer-brand p { color: rgba(255,255,255,0.6); font-size: 14px; max-width: 360px; margin-bottom: 20px; }
.footer-social { display: flex; gap: 10px; }
.footer-social a {
  width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.06);
  display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.7);
}
.footer-social a:hover { background: rgba(255,255,255,0.12); color: #fff; }
.footer-cols { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
.footer-cols b { display: block; color: #fff; font-weight: 700; font-size: 13px; margin-bottom: 14px; }
.footer-cols a { display: block; color: rgba(255,255,255,0.6); font-size: 13px; padding: 5px 0; }
.footer-cols a:hover { color: #fff; text-decoration: none; }
.footer-bottom { display: flex; justify-content: space-between; align-items: center; padding-top: 28px; font-size: 13px; color: rgba(255,255,255,0.5); }
.footer-bottom a { color: rgba(255,255,255,0.5); }
.footer-bottom a:hover { color: #fff; }

/* ---------- Tweaks panel ---------- */
.tweaks-panel {
  position: fixed; bottom: 24px; right: 24px; z-index: 100;
  background: #0F1220; color: #fff; border-radius: 16px;
  padding: 20px; width: 280px;
  box-shadow: 0 24px 60px rgba(0,0,0,0.3);
  border: 1px solid rgba(255,255,255,0.1);
  display: none;
}
.tweaks-panel.on { display: block; }
.tweaks-panel h5 { color: #fff; font-size: 14px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center; }
.tweaks-panel .close { cursor: pointer; opacity: 0.6; }
.tweaks-panel .close:hover { opacity: 1; }
.tweaks-group { margin-bottom: 18px; }
.tweaks-group > label { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.6); margin-bottom: 8px; }
.tweaks-opts { display: flex; flex-direction: column; gap: 4px; }
.tweaks-opts label { display: flex; gap: 8px; align-items: center; font-size: 13px; color: rgba(255,255,255,0.85); cursor: pointer; padding: 6px 8px; border-radius: 6px; }
.tweaks-opts label:hover { background: rgba(255,255,255,0.05); }
.tweaks-opts input { accent-color: var(--accent-gold-300); }

/* ---------- Responsive ---------- */
@media (max-width: 900px) {
  h1 { font-size: 42px; }
  h2 { font-size: 32px; }
  .nav-links { display: none; }
  .hero-inner, .features-row, .features-row.reverse, .contact-inner, .footer-top { grid-template-columns: 1fr; gap: 40px; }
  .features-row.reverse .feature-copy, .features-row.reverse .feature-mock { order: unset; }
  .stats-grid, .products-grid, .testimonial-grid, .cases-grid, .modules-grid, .blog-grid, .pricing-grid, .footer-cols { grid-template-columns: repeat(2, 1fr); }
  .stats-num { font-size: 38px; }
  .section { padding: 72px 0; }
}
@media (max-width: 600px) {
  .stats-grid, .products-grid, .testimonial-grid, .cases-grid, .modules-grid, .blog-grid, .pricing-grid, .footer-cols { grid-template-columns: 1fr; }
}
