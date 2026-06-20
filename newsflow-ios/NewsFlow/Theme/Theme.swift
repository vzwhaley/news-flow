import SwiftUI

/// Brand palette mirroring the Android `Theme.kt` / Tailwind tokens so the two
/// native apps look identical.
enum Brand {
    static let blue      = Color(hex: 0x2563EB)   // primary
    static let blueDark  = Color(hex: 0x1D4ED8)
    static let blueLight = Color(hex: 0xEFF6FF)
    static let ink       = Color(hex: 0x0F172A)
    static let gray500   = Color(hex: 0x64748B)
    static let gray100   = Color(hex: 0xE2E8F0)

    /// Gradient used on the "Read more" pill, matching the web/Android button.
    static let pill = LinearGradient(
        colors: [blue, blueDark],
        startPoint: .leading,
        endPoint: .trailing
    )
}

extension Color {
    /// Build a Color from a 0xRRGGBB literal.
    init(hex: UInt32, alpha: Double = 1.0) {
        let r = Double((hex >> 16) & 0xFF) / 255.0
        let g = Double((hex >> 8) & 0xFF) / 255.0
        let b = Double(hex & 0xFF) / 255.0
        self.init(.sRGB, red: r, green: g, blue: b, opacity: alpha)
    }
}
