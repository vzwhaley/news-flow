import SwiftUI

struct FeedRow: Identifiable {
    let topic: Topic
    let parentName: String?
    var id: Int { topic.id }
}

@MainActor
final class FeedViewModel: ObservableObject {
    @Published var loading = true
    @Published var isPro = false
    @Published var topics: [Topic] = []
    @Published var watchlist: [Article] = []
    @Published var readIds: Set<Int> = []
    @Published var savedFps: Set<String> = []
    @Published var busy = false
    @Published var error: String?

    private var api: NewsFlowAPI { ServiceLocator.shared.api }

    /// Flattened topic + sub-topic rows, in render order.
    var rows: [FeedRow] {
        var out: [FeedRow] = []
        for top in topics {
            out.append(FeedRow(topic: top, parentName: nil))
            for child in top.children {
                out.append(FeedRow(topic: child, parentName: top.name))
            }
        }
        return out
    }

    func load() {
        Task {
            loading = true
            error = nil
            let me = try? await api.me()
            guard let feed = try? await api.feed() else {
                loading = false
                error = "Couldn't load your feed."
                return
            }
            let read = feed.topics.flatMap { collectArticles($0) }.filter { $0.isRead }.map { $0.id }
            isPro = me?.user.isPro ?? false
            topics = feed.topics
            watchlist = feed.watchlist
            readIds = Set(read)
            savedFps = Set(feed.savedFingerprints)
            loading = false
        }
    }

    func addTopic(_ name: String) {
        let trimmed = name.trimmingCharacters(in: .whitespacesAndNewlines)
        guard !trimmed.isEmpty else { return }
        Task {
            busy = true
            error = nil
            do {
                _ = try await api.addTopic(AddTopicRequest(name: trimmed))
                busy = false
                load()
            } catch APIError.http(422) {
                busy = false
                error = "Free accounts can follow up to 2 topics. Upgrade to Pro for unlimited."
            } catch {
                busy = false
                self.error = "Couldn't add that topic."
            }
        }
    }

    func deleteTopic(_ id: Int) {
        Task {
            _ = try? await api.deleteTopic(id)
            load()
        }
    }

    func refreshTopic(_ id: Int) {
        Task {
            busy = true
            _ = try? await api.refreshTopic(id)
            busy = false
            load()
        }
    }

    func markRead(_ article: Article) {
        guard !readIds.contains(article.id) else { return }
        readIds.insert(article.id)
        Task { _ = try? await api.markRead(article.id) }
    }

    func save(_ article: Article) {
        guard !savedFps.contains(article.fingerprint) else { return }
        savedFps.insert(article.fingerprint)
        Task {
            _ = try? await api.save(
                SaveRequest(
                    headline: article.headline,
                    description: article.description,
                    url: article.url,
                    source: article.source,
                    imageUrl: article.imageUrl,
                    topicName: article.topicName
                )
            )
        }
    }

    private func collectArticles(_ t: Topic) -> [Article] {
        t.articles + t.children.flatMap { collectArticles($0) }
    }
}

struct FeedView: View {
    @StateObject private var vm = FeedViewModel()
    @State private var newTopic = ""
    @State private var didLoad = false
    @Environment(\.openURL) private var openURL

    var body: some View {
        Group {
            if vm.loading {
                ProgressView().frame(maxWidth: .infinity, maxHeight: .infinity)
            } else {
                ScrollView {
                    LazyVStack(alignment: .leading, spacing: 10) {
                        addTopicRow

                        if !vm.watchlist.isEmpty {
                            SectionLabel("On your watchlist").padding(.top, 6)
                            ForEach(vm.watchlist) { a in
                                card(for: a, topicLabel: a.topicName)
                            }
                        }

                        ForEach(vm.rows) { row in
                            topicHeader(row)
                                .padding(.top, 6)
                            if row.topic.articles.isEmpty {
                                Text("No articles yet.")
                                    .font(.system(size: 13))
                                    .foregroundColor(Brand.gray500)
                            } else {
                                ForEach(row.topic.articles) { a in
                                    card(for: a, topicLabel: nil)
                                }
                            }
                        }

                        if vm.rows.isEmpty {
                            Text("Add your first topic above — World News, your team, a company, a hobby — and we'll pull today's top stories.")
                                .foregroundColor(Brand.gray500)
                                .padding(.top, 24)
                        }
                    }
                    .padding(16)
                }
            }
        }
        .onAppear {
            if !didLoad { didLoad = true; vm.load() }
        }
    }

    private var addTopicRow: some View {
        VStack(alignment: .leading, spacing: 6) {
            HStack(spacing: 8) {
                TextField("Add a topic", text: $newTopic)
                    .textFieldStyle(.roundedBorder)
                    .onSubmit(submitTopic)
                Button("Add", action: submitTopic)
                    .buttonStyle(.borderedProminent)
                    .disabled(vm.busy || newTopic.trimmingCharacters(in: .whitespaces).isEmpty)
            }
            if let error = vm.error {
                Text(error)
                    .font(.system(size: 13))
                    .foregroundColor(.red)
            }
        }
    }

    private func submitTopic() {
        vm.addTopic(newTopic)
        newTopic = ""
    }

    private func topicHeader(_ row: FeedRow) -> some View {
        HStack(alignment: .center) {
            VStack(alignment: .leading, spacing: 0) {
                if let parent = row.parentName {
                    Text(parent.uppercased())
                        .font(.system(size: 11, weight: .bold))
                        .foregroundColor(Brand.blue)
                }
                Text(row.topic.name)
                    .font(.system(size: 20, weight: .bold))
                    .foregroundColor(Brand.ink)
            }
            Spacer()
            Button { vm.refreshTopic(row.topic.id) } label: {
                Image(systemName: "arrow.clockwise").foregroundColor(Brand.gray500)
            }
            Button { vm.deleteTopic(row.topic.id) } label: {
                Image(systemName: "trash").foregroundColor(Brand.gray500)
            }
        }
    }

    private func card(for a: Article, topicLabel: String?) -> some View {
        ArticleCardView(
            headline: a.headline,
            source: a.source,
            description: a.description,
            topicLabel: topicLabel,
            isRead: vm.readIds.contains(a.id),
            isPro: vm.isPro,
            isSaved: vm.savedFps.contains(a.fingerprint),
            articleId: a.id,
            onOpen: { open(a) },
            onToggleSave: { vm.save(a) }
        )
    }

    private func open(_ a: Article) {
        vm.markRead(a)
        if let url = URL(string: a.url) { openURL(url) }
    }
}
