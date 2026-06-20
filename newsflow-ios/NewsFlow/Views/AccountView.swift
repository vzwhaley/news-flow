import SwiftUI

@MainActor
final class AccountViewModel: ObservableObject {
    @Published var user: User?
    @Published var refreshHour = 6
    @Published var digestEnabled = false
    @Published var digestNewOnly = false
    @Published var saving = false
    @Published var saved = false

    private var api: NewsFlowAPI { ServiceLocator.shared.api }

    func load() {
        Task {
            if let u = try? await api.me().user {
                user = u
                refreshHour = u.refreshHour
                digestEnabled = u.digestEnabled
                digestNewOnly = u.digestNewOnly
            }
        }
    }

    func save() {
        Task {
            saving = true
            _ = try? await api.updatePreferences(
                PreferencesRequest(
                    refreshHour: refreshHour,
                    timezone: TimeZone.current.identifier,
                    digestEnabled: digestEnabled,
                    digestNewOnly: digestNewOnly
                )
            )
            saving = false
            saved = true
        }
    }
}

struct AccountView: View {
    let onSignOut: () -> Void

    @StateObject private var vm = AccountViewModel()
    @State private var didLoad = false
    @Environment(\.openURL) private var openURL

    private var tierLabel: String {
        guard let user = vm.user else { return "" }
        if user.isPro {
            if let tier = user.tier, !tier.isEmpty {
                return "Pro · \(tier.prefix(1).uppercased() + tier.dropFirst())"
            }
            return "Pro"
        }
        return "Free"
    }

    var body: some View {
        ScrollView {
            VStack(spacing: 16) {
                identityCard
                if let user = vm.user, !user.isPro {
                    Button {
                        openURL(AppConfig.pricingURL)
                    } label: {
                        Text("Upgrade to Pro")
                            .fontWeight(.semibold)
                            .frame(maxWidth: .infinity)
                    }
                    .buttonStyle(.borderedProminent)
                }
                preferencesCard

                Button(action: onSignOut) {
                    Text("Sign out").frame(maxWidth: .infinity)
                }
                .buttonStyle(.bordered)

                Text("NewsFlow · by moon whale media, llc")
                    .font(.system(size: 12))
                    .foregroundColor(Brand.gray500)
                    .frame(maxWidth: .infinity, alignment: .leading)
            }
            .padding(20)
        }
        .onAppear {
            if !didLoad { didLoad = true; vm.load() }
        }
    }

    private var identityCard: some View {
        VStack(alignment: .leading, spacing: 0) {
            Text(vm.user?.name ?? "—")
                .font(.system(size: 20, weight: .bold))
                .foregroundColor(Brand.ink)
            Text(vm.user?.email ?? "")
                .font(.system(size: 14))
                .foregroundColor(Brand.gray500)
            Text("Plan: \(tierLabel)")
                .font(.system(size: 14, weight: .semibold))
                .foregroundColor(Brand.blue)
                .padding(.top, 10)
        }
        .padding(18)
        .frame(maxWidth: .infinity, alignment: .leading)
        .background(Brand.gray100.opacity(0.6))
        .clipShape(RoundedRectangle(cornerRadius: 14))
    }

    private var preferencesCard: some View {
        VStack(alignment: .leading, spacing: 12) {
            Text("News preferences")
                .font(.system(size: 16, weight: .semibold))
                .foregroundColor(Brand.ink)

            HStack {
                Text("Daily refresh time")
                    .font(.system(size: 14))
                    .foregroundColor(Brand.ink)
                Spacer()
                Picker("", selection: $vm.refreshHour) {
                    ForEach(0..<24, id: \.self) { h in
                        Text(hourLabel(h)).tag(h)
                    }
                }
                .pickerStyle(.menu)
                .tint(Brand.blue)
            }

            Toggle(isOn: $vm.digestEnabled) {
                Text("Email me a daily digest")
                    .font(.system(size: 14))
                    .foregroundColor(Brand.ink)
            }

            if vm.digestEnabled {
                Toggle(isOn: $vm.digestNewOnly) {
                    Text("Only new headlines")
                        .font(.system(size: 14))
                        .foregroundColor(Brand.ink)
                }
            }

            HStack {
                Button("Save") { vm.save() }
                    .buttonStyle(.borderedProminent)
                    .disabled(vm.saving)
                if vm.saved {
                    Text("Saved.")
                        .font(.system(size: 13))
                        .foregroundColor(Brand.gray500)
                }
            }
            .padding(.top, 4)
        }
        .padding(18)
        .frame(maxWidth: .infinity, alignment: .leading)
        .background(Color(.systemBackground))
        .clipShape(RoundedRectangle(cornerRadius: 14))
        .overlay(
            RoundedRectangle(cornerRadius: 14).stroke(Brand.gray100, lineWidth: 1)
        )
        .onChange(of: vm.refreshHour) { _ in vm.saved = false }
        .onChange(of: vm.digestEnabled) { _ in vm.saved = false }
        .onChange(of: vm.digestNewOnly) { _ in vm.saved = false }
    }

    private func hourLabel(_ h: Int) -> String {
        let ampm = h < 12 ? "AM" : "PM"
        let hr = h % 12 == 0 ? 12 : h % 12
        return "\(hr):00 \(ampm)"
    }
}
