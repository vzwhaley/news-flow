package com.newsflow.android.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.filled.Bolt
import androidx.compose.material.icons.filled.Bookmark
import androidx.compose.material.icons.filled.BookmarkBorder
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.newsflow.android.data.ServiceLocator
import kotlinx.coroutines.launch

@Composable
fun ArticleCard(
    headline: String,
    source: String?,
    description: String,
    topicLabel: String? = null,
    isRead: Boolean = false,
    isPro: Boolean = false,
    isSaved: Boolean = false,
    articleId: Long? = null,
    onOpen: () -> Unit,
    onToggleSave: (() -> Unit)? = null,
) {
    var tldr by remember { mutableStateOf<String?>(null) }
    var tldrLoading by remember { mutableStateOf(false) }
    var tldrShown by remember { mutableStateOf(false) }
    val scope = rememberCoroutineScope()

    fun toggleTldr() {
        if (tldr != null) { tldrShown = !tldrShown; return }
        if (articleId == null) return
        scope.launch {
            tldrLoading = true
            val res = runCatching { ServiceLocator.api.summary(articleId) }.getOrNull()
            tldrLoading = false
            tldr = if (res != null && res.isSuccessful) res.body()?.tldr else "Summary isn't available right now."
            tldrShown = true
        }
    }

    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = Color.Transparent),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
    ) {
        Column(
            Modifier
                .background(
                    Brush.verticalGradient(
                        listOf(MaterialTheme.colorScheme.surface, MaterialTheme.colorScheme.surfaceVariant),
                    ),
                )
                .padding(16.dp),
        ) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                if (topicLabel != null) {
                    Text("$topicLabel · ", fontSize = 12.sp, color = BrandBlue)
                }
                Text(source ?: "", fontSize = 12.sp, color = MaterialTheme.colorScheme.onSurfaceVariant, modifier = Modifier.weight(1f))
                if (onToggleSave != null && (isPro || isSaved)) {
                    IconButton(onClick = onToggleSave, modifier = Modifier.size(28.dp)) {
                        Icon(
                            if (isSaved) Icons.Filled.Bookmark else Icons.Filled.BookmarkBorder,
                            contentDescription = if (isSaved) "Remove from saved" else "Save",
                            tint = if (isSaved) BrandBlue else MaterialTheme.colorScheme.onSurfaceVariant,
                        )
                    }
                }
            }
            Spacer(Modifier.height(4.dp))
            Text(
                headline,
                fontSize = 17.sp,
                fontWeight = FontWeight.SemiBold,
                color = if (isRead) MaterialTheme.colorScheme.onSurfaceVariant else MaterialTheme.colorScheme.onSurface,
            )
            if (description.isNotBlank()) {
                Spacer(Modifier.height(4.dp))
                Text(description, fontSize = 14.sp, color = MaterialTheme.colorScheme.onSurfaceVariant, maxLines = 3, overflow = TextOverflow.Ellipsis)
            }

            if (tldrShown && tldr != null) {
                Spacer(Modifier.height(8.dp))
                Column(Modifier.fillMaxWidth().padding(10.dp)) {
                    Text("TL;DR", fontSize = 11.sp, fontWeight = FontWeight.Bold, color = BrandBlue)
                    Text(tldr!!, fontSize = 13.sp, color = MaterialTheme.colorScheme.onSurface)
                }
            }

            Spacer(Modifier.height(6.dp))
            Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.SpaceBetween, modifier = Modifier.fillMaxWidth()) {
                // Read more — modern gradient pill button
                Row(
                    verticalAlignment = Alignment.CenterVertically,
                    modifier = Modifier
                        .clip(RoundedCornerShape(50))
                        .background(Brush.horizontalGradient(listOf(BrandBlue, Color(0xFF1D4ED8))))
                        .clickable(onClick = onOpen)
                        .padding(horizontal = 16.dp, vertical = 9.dp),
                ) {
                    Text("Read more", fontSize = 13.sp, fontWeight = FontWeight.SemiBold, color = Color.White)
                    Spacer(Modifier.width(4.dp))
                    Icon(Icons.AutoMirrored.Filled.ArrowForward, contentDescription = null, tint = Color.White, modifier = Modifier.size(16.dp))
                }
                if (isPro && articleId != null) {
                    TextButton(onClick = { toggleTldr() }, enabled = !tldrLoading) {
                        if (tldrLoading) {
                            CircularProgressIndicator(modifier = Modifier.size(14.dp), strokeWidth = 2.dp)
                        } else {
                            Icon(Icons.Filled.Bolt, contentDescription = null, tint = MaterialTheme.colorScheme.onSurfaceVariant, modifier = Modifier.size(16.dp))
                            Text(if (tldr != null) "TL;DR" else "TL;DR this", fontSize = 12.sp, color = MaterialTheme.colorScheme.onSurfaceVariant)
                        }
                    }
                }
            }
        }
    }
}
