import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg
            viewBox="0 0 32 32"
            xmlns="http://www.w3.org/2000/svg"
            {...props}
        >
            <rect width="32" height="32" rx="6" fill="#000" />
            <text
                x="16"
                y="21"
                fontFamily="'Space Grotesk', 'Instrument Sans', sans-serif"
                fontWeight="700"
                fontSize="14"
                fill="#fff"
                textAnchor="middle"
            >
                DJ
            </text>
        </svg>
    );
}
