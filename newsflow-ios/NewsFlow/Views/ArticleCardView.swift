import SwiftUI

/// One article tile — the iOS port of the Android `ArticleCard` composable.
/// Crisp white surface with a subtle border/shadow, a gradient "Read more"
/// pill, an optional bookmark, and a Pro-only TL;DR action.
struct ArticleCardView: View {
    let headline: String
    let source: String?
    let description: String
    var topicLabel: String? = nil
    var isRead: Bool = false
    var isPro: Bool = false
    var isSaved: Bool = false
    var articleId: Int? = nil
    let onOpen: () -> Void
    var onToggleSave: (() -> Void)? = nil

    @State private var tldr: String?
    @State private var tldrLoading = false
    @State private var tldrShown = false

    private var api: NewsFlowAPI { ServiceLocator.shared.api }

    var body: some View {
        VStack(alignment: .leading, spacing: 6) {
            // Source row + bookmark
            HStack(spacing: 0) {
                if let topicLabel {
                    Text("\(topicLabel) · ")
                        .font(.system(size: 12))
                        .foregroundColor(Brand.blue)
                }
                Text(source ?? "")
                    .font(.system(size: 12))
                    .foregroundColor(Brand.gray500)
                Spacer(minLength: 0)
                if let onToggleSave, isPro || isSaved {
                    Button(action: onToggleSave) {
                        Image(systemName: isSaved ? "bookmark.fill" : "bookmark")
                            .font(.system(size: 16))
                            .foregroundColor(isSaved ? Brand.blue : Brand.gray500)
                    }
                    .buttonStyle(.plain)
                }
            }

            // Headline
            Text(headline)
                .font(.system(size: 17, weight: .semibold))
                .foregroundColor(isRead ? Brand.gray500 : Brand.ink)
                .fixedSize(horizontal: false, vertical: true)

            // Description
            if !description.isEmpty {
                Text(description)
                    .font(.system(size: 14))
                    .foregroundColor(Brand.gray500)
                    .lineLimit(3)
                    .fixedSize(horizontal: false, vertical: true)
            }

            // TL;DR panel
            if tldrShown, let tldr {
                VStack(alignment: .leading, spacing: 2) {
                    Text("TL;DR")
                        .font(.system(size: 11, weight: .bold))
                        .foregroundColor(Brand.blue)
                    Text(tldr)
                        .font(.system(size: 13))
                        .foregroundColor(Brand.ink)
                }
                .frame(maxWidth: .infinity, alignment: .leading)
                .padding(10)
                .background(Brand.blueLight)
                .clipShape(RoundedRectangle(cornerRadius: 10))
                .padding(.top, 2)
            }

            // Actions
            HStack {
                Button(action: onOpen) {
                    HStack(spacing: 4) {
                        Text("Read more")
                            .font(.system(size: 13, weight: .semibold))
                        Image(systemName: "arrow.right")
                            .font(.system(size: 13, weight: .semibold))
                    }
                    .foregroundColor(.white)
                    .padding(.horizontal, 16)
                    .padding(.vertical, 9)
                    .background(Brand.pill)
                    .clipShape(Capsule())
                }
                .buttonStyle(.plain)

                Spacer()

                if isPro, let articleId {
                    Button(action: { toggleTldr(articleId) }) {
                        HStack(spacing: 4) {
                            if tldrLoading {
                                ProgressView().controlSize(.mini)
                            } else {
                                Image(systemName: "bolt.fill").font(.system(size: 13))
                                Text(tldr != nil ? "TL;DR" : "TL;DR this")
                                    .font(.system(size: 12))
                            }
                        }
                        .foregroundColor(Brand.gray500)
                    }
                    .buttonStyle(.plain)
                    .disabled(tldrLoading)
                }
            }
            .padding(.top, 2)
        }
        .padding(16)
        .background(Color(.systemBackground))
        .clipShape(RoundedRectangle(cornerRadius: 16))
        .overlay(
            RoundedRectangle(cornerRadius: 16)
                .stroke(Brand.gray100, lineWidth: 1)
        )
        .shadow(color: Color.black.opacity(0.04), radius: 6, x: 0, y: 2)
    }

    private func toggleTldr(_ id: Int) {
        if tldr != nil {
            tldrShown.toggle()
            return
        }
        Task {
            tldrLoading = true
            let result = try? await api.summary(id)
            tldrLoading = false
            tldr = result?.tldr ?? "Summary isn't available right now."
            tldrShown = true
        }
    }
}
