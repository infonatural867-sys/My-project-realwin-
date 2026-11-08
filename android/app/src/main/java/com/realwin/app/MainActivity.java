package com.realwin.app;

import android.app.Activity;
import android.os.Bundle;
import android.graphics.Color;
import android.graphics.Typeface;
import android.graphics.drawable.GradientDrawable;
import android.view.Gravity;
import android.view.View;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.ScrollView;
import android.widget.TextView;

public class MainActivity extends Activity {
    private final int ink = Color.rgb(16, 24, 32);
    private final int panel = Color.rgb(28, 40, 50);
    private final int green = Color.rgb(22, 199, 132);
    private LinearLayout root;
    private LinearLayout content;

    @Override public void onCreate(Bundle state) {
        super.onCreate(state);
        getWindow().setStatusBarColor(ink);
        getWindow().setNavigationBarColor(ink);
        showPage("Home");
    }

    private GradientDrawable bg(int color, int radius) {
        GradientDrawable d = new GradientDrawable(); d.setColor(color); d.setCornerRadius(radius); return d;
    }
    private TextView text(String value, int size, int color, boolean bold) {
        TextView t = new TextView(this); t.setText(value); t.setTextSize(size); t.setTextColor(color);
        if (bold) t.setTypeface(Typeface.DEFAULT, Typeface.BOLD); return t;
    }
    private void gap(LinearLayout box, int height) {
        View v = new View(this); box.addView(v, new LinearLayout.LayoutParams(1, height));
    }
    private void card(String heading, String body) {
        LinearLayout c = new LinearLayout(this); c.setOrientation(LinearLayout.VERTICAL); c.setPadding(18,16,18,16); c.setBackground(bg(panel, 22));
        c.addView(text(heading, 17, Color.WHITE, true)); gap(c, 8); c.addView(text(body, 14, Color.rgb(190,202,210), false));
        LinearLayout.LayoutParams p = new LinearLayout.LayoutParams(-1, -2); p.bottomMargin = 12; content.addView(c,p);
    }
    private void action(String label, Runnable run) {
        Button b = new Button(this); b.setText(label); b.setAllCaps(false); b.setTextColor(Color.WHITE); b.setBackground(bg(green, 16));
        LinearLayout.LayoutParams p = new LinearLayout.LayoutParams(-1, -2); p.bottomMargin = 10; content.addView(b,p); b.setOnClickListener(v -> run.run());
    }
    private void showPage(String page) {
        root = new LinearLayout(this); root.setOrientation(LinearLayout.VERTICAL); root.setPadding(18,18,18,12); root.setBackgroundColor(ink);
        LinearLayout header = new LinearLayout(this); header.setGravity(Gravity.CENTER_VERTICAL);
        TextView brand = text("REALWIN", 24, green, true); header.addView(brand);
        TextView tag = text("  •  PLAY FOR FUN", 10, Color.LTGRAY, true); header.addView(tag); root.addView(header); gap(root,20);
        ScrollView scroll = new ScrollView(this); content = new LinearLayout(this); content.setOrientation(LinearLayout.VERTICAL); scroll.addView(content);
        content.addView(text(page, 26, Color.WHITE, true)); gap(content,14);
        if (page.equals("Home")) {
            card("YOUR POINTS", "0 points\nEntertainment points only. No cash value.");
            card("WELCOME TO REALWIN", "A fresh entertainment experience. Sign-in and online services will be enabled after your own backend is configured.");
            action("Explore games", () -> showPage("Games"));
            action("View point history", () -> showPage("History"));
            action("My profile", () -> showPage("Profile"));
        } else if (page.equals("Games")) {
            card("GAMES", "Game catalogue will appear here after approved game modules are added.");
            card("POINTS RULE", "Points are non-transferable, non-redeemable entertainment points. No wagering or cash prizes.");
        } else if (page.equals("History")) {
            card("POINT HISTORY", "No point activity yet. The live history will load from your backend when connected.");
        } else {
            card("PROFILE", "Your account profile will be available after secure registration and login are connected to the backend.");
        }
        root.addView(scroll, new LinearLayout.LayoutParams(-1,0,1));
        LinearLayout nav = new LinearLayout(this); nav.setGravity(Gravity.CENTER); nav.setPadding(0,10,0,0);
        String[] tabs = {"Home", "Games", "History", "Profile"};
        for (String tab : tabs) { TextView item = text(tab, 12, tab.equals(page)?green:Color.LTGRAY, true); item.setGravity(Gravity.CENTER); item.setPadding(8,12,8,12); nav.addView(item,new LinearLayout.LayoutParams(0,-2,1)); item.setOnClickListener(v -> showPage(tab)); }
        root.addView(nav); setContentView(root);
    }
}
